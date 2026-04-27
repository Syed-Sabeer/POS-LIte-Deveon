<?php

namespace App\Services\V2;

use App\Models\V2\Account;
use App\Models\V2\Invoice;
use App\Models\V2\LedgerEntry;
use App\Models\V2\StockMovement;
use App\Models\V2\Voucher;
use RuntimeException;

class PostingService
{
    public function postInvoice(Invoice $invoice, ?int $userId = null): void
    {
        $invoice->load(['account', 'items.item']);
        $this->clearSource('invoice', (int) $invoice->id);

        if ($invoice->status !== 'posted') {
            return;
        }

        if ($invoice->type === 'purchase') {
            $this->postPurchaseInvoice($invoice, $userId);
            return;
        }

        $this->postSaleInvoice($invoice, $userId);
    }

    public function postVoucher(Voucher $voucher, ?int $userId = null): void
    {
        $voucher->load(['account', 'contraAccount', 'lines.account']);
        $this->clearSource('voucher', (int) $voucher->id);

        if ($voucher->status !== 'posted') {
            return;
        }

        if ($voucher->type === 'receipt') {
            $this->postLedger($voucher->contra_account_id, 'voucher', (int) $voucher->id, (string) $voucher->voucher_date, $voucher->voucher_no, $voucher->particulars ?: 'Receipt', (float) $voucher->amount, 0, $userId);
            $this->postLedger($voucher->account_id, 'voucher', (int) $voucher->id, (string) $voucher->voucher_date, $voucher->voucher_no, $voucher->particulars ?: 'Receipt', 0, (float) $voucher->amount, $userId);
            return;
        }

        if ($voucher->type === 'payment') {
            $this->postLedger($voucher->account_id, 'voucher', (int) $voucher->id, (string) $voucher->voucher_date, $voucher->voucher_no, $voucher->particulars ?: 'Payment', (float) $voucher->amount, 0, $userId);
            $this->postLedger($voucher->contra_account_id, 'voucher', (int) $voucher->id, (string) $voucher->voucher_date, $voucher->voucher_no, $voucher->particulars ?: 'Payment', 0, (float) $voucher->amount, $userId);
            return;
        }

        foreach ($voucher->lines as $line) {
            $this->postLedger(
                (int) $line->account_id,
                'voucher',
                (int) $voucher->id,
                (string) ($line->post_date ?: $voucher->voucher_date),
                $voucher->voucher_no,
                $line->particulars ?: $voucher->remarks ?: 'Journal Voucher',
                (float) $line->debit,
                (float) $line->credit,
                $userId
            );
        }
    }

    public function clearSource(string $sourceType, int $sourceId): void
    {
        LedgerEntry::where('source_type', $sourceType)->where('source_id', $sourceId)->delete();
        StockMovement::where('source_type', $sourceType)->where('source_id', $sourceId)->delete();
    }

    public function postLedger(
        int $accountId,
        string $sourceType,
        int $sourceId,
        string $date,
        ?string $voucherNo,
        ?string $particulars,
        float $debit,
        float $credit,
        ?int $userId
    ): LedgerEntry {
        $previous = (float) LedgerEntry::where('account_id', $accountId)->orderByDesc('id')->value('running_balance');

        return LedgerEntry::create([
            'account_id' => $accountId,
            'source_type' => $sourceType,
            'source_id' => $sourceId,
            'entry_date' => $date,
            'voucher_no' => $voucherNo,
            'particulars' => $particulars,
            'debit' => $debit,
            'credit' => $credit,
            'running_balance' => $previous + $debit - $credit,
            'created_by' => $userId,
        ]);
    }

    private function postPurchaseInvoice(Invoice $invoice, ?int $userId): void
    {
        $inventory = $this->systemAccount('AST00001');

        $this->postLedger($inventory->id, 'invoice', (int) $invoice->id, (string) $invoice->invoice_date, $invoice->voucher_no, 'Purchase invoice ' . $invoice->voucher_no, (float) $invoice->net_amount, 0, $userId);
        $this->postLedger($invoice->account_id, 'invoice', (int) $invoice->id, (string) $invoice->invoice_date, $invoice->voucher_no, 'Purchase invoice ' . $invoice->voucher_no, 0, (float) $invoice->net_amount, $userId);

        foreach ($invoice->items as $line) {
            if (! $line->item_id) {
                continue;
            }

            StockMovement::create([
                'item_id' => $line->item_id,
                'source_type' => 'invoice',
                'source_id' => $invoice->id,
                'movement_date' => $invoice->invoice_date,
                'voucher_no' => $invoice->voucher_no,
                'account_id' => $invoice->account_id,
                'qty_in' => (float) $line->qty,
                'qty_out' => 0,
                'rate' => (float) $line->discounted_rate,
                'amount' => (float) $line->amount,
                'packing' => $line->item?->packing,
                'remarks' => $line->item_detail,
                'created_by' => $userId,
            ]);
        }
    }

    private function postSaleInvoice(Invoice $invoice, ?int $userId): void
    {
        $sales = $this->systemAccount('REV00001');
        $cash = $this->systemAccount('CBK00001');
        $discount = $this->systemAccount('EXP00002');
        $inventory = $this->systemAccount('AST00001');
        $cogs = $this->systemAccount('EXP00003');

        $salesCredit = (float) $invoice->gross_amount + (float) $invoice->charges;
        $this->postLedger($invoice->account_id, 'invoice', (int) $invoice->id, (string) $invoice->invoice_date, $invoice->voucher_no, 'Sale invoice ' . $invoice->voucher_no, (float) $invoice->net_amount, 0, $userId);
        if ((float) $invoice->discount > 0) {
            $this->postLedger($discount->id, 'invoice', (int) $invoice->id, (string) $invoice->invoice_date, $invoice->voucher_no, 'Sale discount ' . $invoice->voucher_no, (float) $invoice->discount, 0, $userId);
        }
        $this->postLedger($sales->id, 'invoice', (int) $invoice->id, (string) $invoice->invoice_date, $invoice->voucher_no, 'Sale invoice ' . $invoice->voucher_no, 0, $salesCredit, $userId);

        if ((float) $invoice->received_amount > 0) {
            $received = min((float) $invoice->received_amount, (float) $invoice->net_amount);
            $this->postLedger($cash->id, 'invoice', (int) $invoice->id, (string) $invoice->invoice_date, $invoice->voucher_no, 'Sale received ' . $invoice->voucher_no, $received, 0, $userId);
            $this->postLedger($invoice->account_id, 'invoice', (int) $invoice->id, (string) $invoice->invoice_date, $invoice->voucher_no, 'Sale received ' . $invoice->voucher_no, 0, $received, $userId);
        }

        $costTotal = 0;
        foreach ($invoice->items as $line) {
            if (! $line->item_id) {
                continue;
            }

            $cost = (float) ($line->item?->cost ?? 0);
            $costTotal += $cost * (float) $line->qty;

            StockMovement::create([
                'item_id' => $line->item_id,
                'source_type' => 'invoice',
                'source_id' => $invoice->id,
                'movement_date' => $invoice->invoice_date,
                'voucher_no' => $invoice->voucher_no,
                'account_id' => $invoice->account_id,
                'qty_in' => 0,
                'qty_out' => (float) $line->qty,
                'rate' => $cost,
                'amount' => $cost * (float) $line->qty,
                'packing' => $line->item?->packing,
                'remarks' => $line->item_detail,
                'created_by' => $userId,
            ]);
        }

        if (config('accounting_v2.costing_enabled') && $costTotal > 0) {
            $this->postLedger($cogs->id, 'invoice', (int) $invoice->id, (string) $invoice->invoice_date, $invoice->voucher_no, 'COGS ' . $invoice->voucher_no, $costTotal, 0, $userId);
            $this->postLedger($inventory->id, 'invoice', (int) $invoice->id, (string) $invoice->invoice_date, $invoice->voucher_no, 'Inventory out ' . $invoice->voucher_no, 0, $costTotal, $userId);
        }
    }

    private function systemAccount(string $code): Account
    {
        $account = Account::where('code', $code)->first();

        if (! $account) {
            throw new RuntimeException('V2 system account missing: ' . $code);
        }

        return $account;
    }
}
