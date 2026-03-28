<?php

namespace App\Services\Accounting;

use App\Models\Account;
use App\Models\AccountLedgerEntry;

class LedgerService
{
    public function postAccountLedger(
        int $accountId,
        string $entryDate,
        ?string $sourceType,
        ?int $sourceId,
        ?int $journalEntryId,
        ?string $referenceNo,
        ?string $description,
        float $debit,
        float $credit
    ): AccountLedgerEntry {
        $account = Account::findOrFail($accountId);

        $previousBalance = (float) AccountLedgerEntry::query()
            ->where('account_id', $accountId)
            ->orderByDesc('id')
            ->value('running_balance');

        $normal = $account->normal_balance ?: (in_array($account->account_type ?: $account->type, ['asset', 'expense'], true) ? 'debit' : 'credit');
        $delta = $normal === 'debit' ? ($debit - $credit) : ($credit - $debit);

        return AccountLedgerEntry::create([
            'account_id' => $accountId,
            'entry_date' => $entryDate,
            'source_type' => $sourceType,
            'source_id' => $sourceId,
            'journal_entry_id' => $journalEntryId,
            'reference_no' => $referenceNo,
            'description' => $description,
            'debit' => $debit,
            'credit' => $credit,
            'running_balance' => $previousBalance + $delta,
        ]);
    }
}
