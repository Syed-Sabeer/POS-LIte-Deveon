<?php

namespace App\Services\Accounting;

use App\Models\Account;
use App\Models\AccountTransaction;
use App\Models\JournalEntry;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class ManualAccountingEntryService
{
    public function __construct(private readonly LedgerService $ledgerService)
    {
    }

    public function post(
        string $entryDate,
        string $referenceNo,
        string $voucherType,
        string $description,
        array $lines,
        ?int $createdBy
    ): JournalEntry {
        $debitTotal = array_sum(array_map(fn ($line) => (float) ($line['debit'] ?? 0), $lines));
        $creditTotal = array_sum(array_map(fn ($line) => (float) ($line['credit'] ?? 0), $lines));

        if ($debitTotal <= 0 || $creditTotal <= 0 || abs($debitTotal - $creditTotal) > 0.01) {
            throw new InvalidArgumentException('Accounting entry must have equal debit and credit totals.');
        }

        return DB::transaction(function () use ($entryDate, $referenceNo, $voucherType, $description, $lines, $createdBy) {
            $journal = JournalEntry::create([
                'entry_date' => $entryDate,
                'reference_no' => $referenceNo,
                'source_type' => 'manual_accounting',
                'source_id' => null,
                'voucher_type' => $voucherType,
                'voucher_id' => null,
                'description' => $description,
                'status' => 'posted',
                'created_by' => $createdBy,
            ]);

            $journal->update([
                'source_id' => $journal->id,
                'voucher_id' => $journal->id,
            ]);

            foreach ($lines as $line) {
                $debit = (float) ($line['debit'] ?? 0);
                $credit = (float) ($line['credit'] ?? 0);
                $lineDescription = (string) ($line['description'] ?? $description);
                $accountId = (int) $line['account_id'];

                $journal->lines()->create([
                    'account_id' => $accountId,
                    'debit' => $debit,
                    'credit' => $credit,
                    'description' => $lineDescription,
                ]);

                $this->postAccountTransaction(
                    $accountId,
                    $entryDate,
                    $voucherType,
                    (int) $journal->id,
                    $referenceNo,
                    $lineDescription,
                    $debit,
                    $credit,
                    $createdBy
                );

                $this->ledgerService->postAccountLedger(
                    $accountId,
                    $entryDate,
                    $voucherType,
                    (int) $journal->id,
                    (int) $journal->id,
                    $referenceNo,
                    $lineDescription,
                    $debit,
                    $credit
                );
            }

            return $journal->load('lines.account');
        });
    }

    private function postAccountTransaction(
        int $accountId,
        string $date,
        string $voucherType,
        int $voucherId,
        string $referenceNo,
        string $description,
        float $debit,
        float $credit,
        ?int $createdBy
    ): void {
        $account = Account::findOrFail($accountId);
        $previousBalance = (float) AccountTransaction::where('account_id', $accountId)
            ->orderByDesc('id')
            ->value('balance');

        $normal = $account->normal_balance
            ?: (in_array($account->account_type ?: $account->type, ['asset', 'expense'], true) ? 'debit' : 'credit');
        $delta = $normal === 'debit' ? ($debit - $credit) : ($credit - $debit);

        AccountTransaction::create([
            'account_id' => $accountId,
            'transaction_date' => $date,
            'voucher_type' => $voucherType,
            'voucher_id' => $voucherId,
            'reference_no' => $referenceNo,
            'description' => $description,
            'debit' => $debit,
            'credit' => $credit,
            'balance' => $previousBalance + $delta,
            'created_by' => $createdBy,
        ]);
    }
}
