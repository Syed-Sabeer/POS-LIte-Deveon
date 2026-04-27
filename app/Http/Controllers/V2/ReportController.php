<?php

namespace App\Http\Controllers\V2;

use App\Http\Controllers\Controller;
use App\Models\V2\Account;
use App\Models\V2\Invoice;
use App\Models\V2\Item;
use App\Models\V2\LedgerEntry;
use App\Models\V2\StockMovement;
use App\Models\V2\Voucher;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    public function index()
    {
        $reports = [
            'Account Reports' => [
                'account-ledger-detail' => 'Account Ledger Detail',
                'account-ledger-detailed-analysis' => 'Account Ledger Detailed Analysis',
                'receivables-summary' => 'Receivables Summary',
                'payables-summary' => 'Payables Summary',
                'assets-summary' => 'Assets Summary',
                'daily-cash-sheet' => 'Daily Cash Sheet',
                'daily-credit-sheet' => 'Daily Credit Sheet',
            ],
            'Sales/Purchase Reports' => [
                'sales-aging' => 'Sales Aging Report',
                'customer-wise-sales' => 'Customer-wise Sales Report',
                'inventory-wise-sales' => 'Inventory-wise Sales Report',
                'purchase-challan' => 'Purchase Challan',
                'sale-invoice' => 'Sale Invoice',
                'delivery-challan' => 'Delivery Challan',
                'gate-pass' => 'Gate Pass',
            ],
            'Stock Reports' => [
                'stock-ledger-detail' => 'Stock Ledger Detail',
                'stock-ledger-monthly' => 'Stock Ledger Monthly Report',
                'stock-ledger-summary' => 'Stock Ledger Summary',
                'active-items-summary' => 'Active Items Summary Without Amount',
                'item-wise-profit-loss' => 'Item-wise Profit or Loss',
            ],
            'Voucher Reports' => [
                'receipt-voucher' => 'Receipt Voucher',
                'receipts-journal' => 'Receipts Journal',
                'payment-voucher' => 'Payment Voucher',
                'payments-journal' => 'Payments Journal',
                'journal-voucher' => 'Journal Voucher',
            ],
            'Financial Statements' => [
                'trial-balance' => 'Trial Balance Summary',
                'trial-balance-detail' => 'Trial Balance Detail',
                'aged-trial-balance' => 'Aged Trial Balance',
                'income-statement' => 'Profit & Loss Statement',
                'balance-sheet' => 'Balance Sheet',
            ],
        ];

        return view('v2.reports.index', compact('reports'));
    }

    public function show(Request $request, string $report)
    {
        $from = $request->from_date;
        $to = $request->to_date;

        $accounts = Account::orderBy('name')->get();
        $statementAccounts = Account::with('ledgerEntries')->orderBy('code')->get();
        $items = Item::orderBy('description')->get();

        $ledgerEntries = LedgerEntry::with('account')
            ->when($from, fn ($q) => $q->whereDate('entry_date', '>=', $from))
            ->when($to, fn ($q) => $q->whereDate('entry_date', '<=', $to))
            ->when($request->account_id, fn ($q, $id) => $q->where('account_id', $id))
            ->orderBy('entry_date')
            ->get();

        $stockMovements = StockMovement::with(['item', 'account'])
            ->when($from, fn ($q) => $q->whereDate('movement_date', '>=', $from))
            ->when($to, fn ($q) => $q->whereDate('movement_date', '<=', $to))
            ->when($request->item_id, fn ($q, $id) => $q->where('item_id', $id))
            ->orderBy('movement_date')
            ->get();

        $invoices = Invoice::with('account')
            ->when($from, fn ($q) => $q->whereDate('invoice_date', '>=', $from))
            ->when($to, fn ($q) => $q->whereDate('invoice_date', '<=', $to))
            ->latest('invoice_date')
            ->get();

        $vouchers = Voucher::with(['account', 'contraAccount', 'lines.account'])
            ->when($from, fn ($q) => $q->whereDate('voucher_date', '>=', $from))
            ->when($to, fn ($q) => $q->whereDate('voucher_date', '<=', $to))
            ->latest('voucher_date')
            ->get();

        return view('v2.reports.show', compact('report', 'accounts', 'statementAccounts', 'items', 'ledgerEntries', 'stockMovements', 'invoices', 'vouchers'));
    }
}
