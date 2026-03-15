<?php

namespace App\Services\Accounting;

use App\Models\Account;
use App\Models\AccountTransaction;
use App\Models\CustomerPayment;
use App\Models\JournalEntry;
use App\Models\PartyLedger;
use App\Models\PosOrder;
use App\Models\PurchaseInvoice;
use App\Models\SupplierPayment;

class PostingService
{
    public function postSale(PosOrder $order, ?int $cashAccountId, ?int $createdBy): void
    {
        if (JournalEntry::where('voucher_type', 'sale')->where('voucher_id', $order->id)->exists()) {
            return;
        }

        $salesAccount = $this->systemAccountId(Account::CODE_SALES_REVENUE);
        $arAccount = $this->systemAccountId(Account::CODE_RECEIVABLE);
        $cashAccount = $cashAccountId ?: $this->systemAccountId(Account::CODE_CASH);

        $lines = [];
        if ((float) $order->paid_amount > 0) {
            $lines[] = [
                'account_id' => $cashAccount,
                'debit' => (float) $order->paid_amount,
                'credit' => 0,
                'description' => 'Cash received on sale',
            ];
        }

        if ((float) $order->due_amount > 0) {
            $lines[] = [
                'account_id' => $arAccount,
                'debit' => (float) $order->due_amount,
                'credit' => 0,
                'description' => 'Receivable booked on sale',
            ];

            if ($order->customer_id) {
                $this->postPartyLedger(
                    PartyLedger::TYPE_CUSTOMER,
                    (int) $order->customer_id,
                    (string) $order->invoice_date,
                    'sale',
                    $order->id,
                    $order->order_number,
                    'Credit sale invoice ' . $order->order_number,
                    (float) $order->due_amount,
                    0,
                    $createdBy
                );
            }
        }

        $lines[] = [
            'account_id' => $salesAccount,
            'debit' => 0,
            'credit' => (float) $order->total,
            'description' => 'Sales revenue',
        ];

        $this->createJournalWithLines(
            (string) $order->invoice_date,
            $order->order_number,
            'sale',
            $order->id,
            'Sale invoice ' . $order->order_number,
            $lines,
            $createdBy
        );

        $this->postSaleCogs($order, $createdBy);
    }

    public function postCustomerPayment(CustomerPayment $payment, ?int $createdBy): void
    {
        if (JournalEntry::where('voucher_type', 'sale_payment')->where('voucher_id', $payment->id)->exists()) {
            return;
        }

        $arAccount = $this->systemAccountId(Account::CODE_RECEIVABLE);
        $cashAccount = $payment->account_id ?: $this->systemAccountId(Account::CODE_CASH);

        $this->createJournalWithLines(
            (string) $payment->payment_date,
            $payment->reference_no,
            'sale_payment',
            $payment->id,
            'Customer payment receipt ' . $payment->reference_no,
            [
                ['account_id' => $cashAccount, 'debit' => (float) $payment->amount, 'credit' => 0, 'description' => 'Cash/Bank receipt'],
                ['account_id' => $arAccount, 'debit' => 0, 'credit' => (float) $payment->amount, 'description' => 'Accounts receivable settlement'],
            ],
            $createdBy
        );

        $this->postPartyLedger(
            PartyLedger::TYPE_CUSTOMER,
            (int) $payment->customer_id,
            (string) $payment->payment_date,
            'sale_payment',
            $payment->id,
            $payment->reference_no,
            'Payment received from customer',
            0,
            (float) $payment->amount,
            $createdBy
        );
    }

    public function postPurchase(PurchaseInvoice $invoice, ?int $createdBy): void
    {
        if (JournalEntry::where('voucher_type', 'purchase')->where('voucher_id', $invoice->id)->exists()) {
            return;
        }

        $inventoryAccount = $this->systemAccountId(Account::CODE_INVENTORY);
        $apAccount = $this->systemAccountId(Account::CODE_PAYABLE);

        $this->createJournalWithLines(
            (string) $invoice->invoice_date,
            $invoice->invoice_number,
            'purchase',
            $invoice->id,
            'Purchase invoice ' . $invoice->invoice_number,
            [
                ['account_id' => $inventoryAccount, 'debit' => (float) $invoice->total, 'credit' => 0, 'description' => 'Inventory purchased'],
                ['account_id' => $apAccount, 'debit' => 0, 'credit' => (float) $invoice->total, 'description' => 'Accounts payable recognized'],
            ],
            $createdBy
        );

        $this->postPartyLedger(
            PartyLedger::TYPE_SUPPLIER,
            (int) $invoice->supplier_id,
            (string) $invoice->invoice_date,
            'purchase',
            $invoice->id,
            $invoice->invoice_number,
            'Purchase booked against supplier',
            0,
            (float) $invoice->total,
            $createdBy
        );

        if ((float) $invoice->paid_amount > 0) {
            $cashAccount = $this->systemAccountId(Account::CODE_CASH);

            $this->createJournalWithLines(
                (string) $invoice->invoice_date,
                $invoice->invoice_number . '-PAY',
                'purchase_payment',
                $invoice->id,
                'Initial payment on purchase ' . $invoice->invoice_number,
                [
                    ['account_id' => $apAccount, 'debit' => (float) $invoice->paid_amount, 'credit' => 0, 'description' => 'Accounts payable settlement'],
                    ['account_id' => $cashAccount, 'debit' => 0, 'credit' => (float) $invoice->paid_amount, 'description' => 'Cash paid'],
                ],
                $createdBy
            );

            $this->postPartyLedger(
                PartyLedger::TYPE_SUPPLIER,
                (int) $invoice->supplier_id,
                (string) $invoice->invoice_date,
                'purchase_payment',
                $invoice->id,
                $invoice->invoice_number . '-PAY',
                'Initial payment on purchase invoice',
                (float) $invoice->paid_amount,
                0,
                $createdBy
            );
        }
    }

    public function postSupplierPayment(SupplierPayment $payment, ?int $createdBy): void
    {
        if (JournalEntry::where('voucher_type', 'purchase_payment')->where('voucher_id', $payment->id)->exists()) {
            return;
        }

        $apAccount = $this->systemAccountId(Account::CODE_PAYABLE);
        $cashAccount = $payment->account_id ?: $this->systemAccountId(Account::CODE_CASH);

        $this->createJournalWithLines(
            (string) $payment->payment_date,
            $payment->reference_no,
            'purchase_payment',
            $payment->id,
            'Supplier payment ' . $payment->reference_no,
            [
                ['account_id' => $apAccount, 'debit' => (float) $payment->amount, 'credit' => 0, 'description' => 'Accounts payable settlement'],
                ['account_id' => $cashAccount, 'debit' => 0, 'credit' => (float) $payment->amount, 'description' => 'Cash/Bank paid'],
            ],
            $createdBy
        );

        $this->postPartyLedger(
            PartyLedger::TYPE_SUPPLIER,
            (int) $payment->supplier_id,
            (string) $payment->payment_date,
            'purchase_payment',
            $payment->id,
            $payment->reference_no,
            'Payment made to supplier',
            (float) $payment->amount,
            0,
            $createdBy
        );
    }

    private function postSaleCogs(PosOrder $order, ?int $createdBy): void
    {
        $inventoryAccount = $this->systemAccountId(Account::CODE_INVENTORY);
        $cogsAccount = $this->systemAccountId(Account::CODE_COGS);

        $costTotal = (float) $order->items()
            ->with('product:id,cost_price')
            ->get()
            ->sum(fn ($item) => ((float) ($item->product->cost_price ?? 0)) * (int) $item->quantity);

        if ($costTotal <= 0) {
            return;
        }

        $this->createJournalWithLines(
            (string) $order->invoice_date,
            $order->order_number . '-COGS',
            'sale_cogs',
            $order->id,
            'COGS for invoice ' . $order->order_number,
            [
                ['account_id' => $cogsAccount, 'debit' => $costTotal, 'credit' => 0, 'description' => 'Cost of goods sold'],
                ['account_id' => $inventoryAccount, 'debit' => 0, 'credit' => $costTotal, 'description' => 'Inventory reduction'],
            ],
            $createdBy
        );
    }

    private function createJournalWithLines(
        string $date,
        string $referenceNo,
        string $voucherType,
        int $voucherId,
        string $description,
        array $lines,
        ?int $createdBy
    ): void {
        $journal = JournalEntry::create([
            'entry_date' => $date,
            'reference_no' => $referenceNo,
            'voucher_type' => $voucherType,
            'voucher_id' => $voucherId,
            'description' => $description,
            'created_by' => $createdBy,
        ]);

        foreach ($lines as $line) {
            $journal->lines()->create($line);

            $this->postAccountTransaction(
                (int) $line['account_id'],
                $date,
                $voucherType,
                $voucherId,
                $referenceNo,
                (string) ($line['description'] ?? $description),
                (float) $line['debit'],
                (float) $line['credit'],
                $createdBy
            );
        }
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
        $previousBalance = (float) AccountTransaction::where('account_id', $accountId)->orderByDesc('id')->value('balance');

        $accountType = (string) Account::whereKey($accountId)->value('type');
        $delta = in_array($accountType, ['asset', 'expense'], true)
            ? ($debit - $credit)
            : ($credit - $debit);

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

    private function postPartyLedger(
        string $partyType,
        int $partyId,
        string $entryDate,
        string $voucherType,
        int $voucherId,
        string $referenceNo,
        string $description,
        float $debit,
        float $credit,
        ?int $createdBy
    ): void {
        $previousBalance = (float) PartyLedger::query()
            ->where('party_type', $partyType)
            ->where('party_id', $partyId)
            ->orderByDesc('id')
            ->value('balance');

        $balance = $previousBalance + ($debit - $credit);

        PartyLedger::create([
            'party_type' => $partyType,
            'party_id' => $partyId,
            'entry_date' => $entryDate,
            'voucher_type' => $voucherType,
            'voucher_id' => $voucherId,
            'reference_no' => $referenceNo,
            'description' => $description,
            'debit' => $debit,
            'credit' => $credit,
            'balance' => $balance,
            'created_by' => $createdBy,
        ]);
    }

    private function systemAccountId(string $code): int
    {
        $id = Account::query()->where('code', $code)->value('id');

        if (! $id) {
            throw new \RuntimeException('System account not found for code: ' . $code);
        }

        return (int) $id;
    }
}
