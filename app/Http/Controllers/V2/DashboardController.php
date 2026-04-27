<?php

namespace App\Http\Controllers\V2;

use App\Http\Controllers\Controller;

class DashboardController extends Controller
{
    public function index()
    {
        $groups = [
            'Daily Books' => [
                ['label' => 'Purchase Invoices', 'route' => 'v2.purchase.index', 'permission' => 'v2 purchase book', 'icon' => 'ti ti-file-invoice'],
                ['label' => 'Sale Invoices', 'route' => 'v2.sales.index', 'permission' => 'v2 sale bill book', 'icon' => 'ti ti-receipt'],
                ['label' => 'Receipts', 'route' => 'v2.receipts.index', 'permission' => 'v2 receipt vouchers', 'icon' => 'ti ti-cash'],
                ['label' => 'Payments', 'route' => 'v2.payments.index', 'permission' => 'v2 payment vouchers', 'icon' => 'ti ti-cash-banknote'],
                ['label' => 'Journal Vouchers', 'route' => 'v2.journal.index', 'permission' => 'v2 journal vouchers', 'icon' => 'ti ti-notebook'],
            ],
            'Special Books' => [
                ['label' => 'Assets Ledger', 'route' => 'v2.ledgers.summary', 'params' => ['asset'], 'permission' => 'v2 assets ledger', 'icon' => 'ti ti-building'],
                ['label' => 'Liabilities Ledger', 'route' => 'v2.ledgers.summary', 'params' => ['liability'], 'permission' => 'v2 liabilities ledger', 'icon' => 'ti ti-scale'],
                ['label' => 'Capital Ledger', 'route' => 'v2.ledgers.summary', 'params' => ['capital'], 'permission' => 'v2 capital ledger', 'icon' => 'ti ti-briefcase'],
                ['label' => 'Expenses Ledger', 'route' => 'v2.ledgers.summary', 'params' => ['expense'], 'permission' => 'v2 expenses ledger', 'icon' => 'ti ti-report'],
                ['label' => 'Revenue Ledger', 'route' => 'v2.ledgers.summary', 'params' => ['revenue'], 'permission' => 'v2 revenue ledger', 'icon' => 'ti ti-chart-line'],
                ['label' => 'Stock Ledger', 'route' => 'v2.stock-ledger.index', 'permission' => 'v2 stock ledger', 'icon' => 'ti ti-stack'],
                ['label' => 'Accounts Manager', 'route' => 'v2.accounts.index', 'permission' => 'v2 accounts manager', 'icon' => 'ti ti-users'],
                ['label' => 'Stock Manager', 'route' => 'v2.items.index', 'permission' => 'v2 stock manager', 'icon' => 'ti ti-package'],
            ],
            'Utilities' => [
                ['label' => 'Add/Remove Users', 'route' => 'v2.users.index', 'permission' => 'v2 add remove users', 'icon' => 'ti ti-user-cog'],
                ['label' => 'Backup', 'route' => 'v2.utilities.backup', 'permission' => 'v2 backup restore', 'icon' => 'ti ti-database-export'],
                ['label' => 'Restore', 'route' => 'v2.utilities.restore', 'permission' => 'v2 backup restore', 'icon' => 'ti ti-database-import'],
            ],
            'Financial Statements' => [
                ['label' => 'Trial Balance', 'route' => 'v2.reports.show', 'params' => ['trial-balance'], 'permission' => 'v2 trial balance', 'icon' => 'ti ti-list-details'],
                ['label' => 'Aged Trial Balance', 'route' => 'v2.reports.show', 'params' => ['aged-trial-balance'], 'permission' => 'v2 trial balance aging', 'icon' => 'ti ti-calendar-stats'],
                ['label' => 'Income Statement', 'route' => 'v2.reports.show', 'params' => ['income-statement'], 'permission' => 'v2 income statement', 'icon' => 'ti ti-chart-bar'],
                ['label' => 'Balance Sheet', 'route' => 'v2.reports.show', 'params' => ['balance-sheet'], 'permission' => 'v2 balance sheet', 'icon' => 'ti ti-report-money'],
            ],
        ];

        return view('v2.dashboard', compact('groups'));
    }
}
