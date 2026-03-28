<?php

namespace Database\Seeders;

use App\Models\Account;
use Illuminate\Database\Seeder;

class ChartOfAccountsSeeder extends Seeder
{
    public function run(): void
    {
        $accounts = [
            ['name' => 'Cash in Hand', 'code' => Account::CODE_CASH, 'type' => 'asset', 'normal' => 'debit'],
            ['name' => 'Bank Account', 'code' => Account::CODE_BANK, 'type' => 'asset', 'normal' => 'debit'],
            ['name' => 'Accounts Receivable', 'code' => Account::CODE_RECEIVABLE, 'type' => 'asset', 'normal' => 'debit'],
            ['name' => 'Inventory', 'code' => Account::CODE_INVENTORY, 'type' => 'asset', 'normal' => 'debit'],
            ['name' => 'Accounts Payable', 'code' => Account::CODE_PAYABLE, 'type' => 'liability', 'normal' => 'credit'],
            ['name' => 'Owner Equity', 'code' => '3101', 'type' => 'equity', 'normal' => 'credit'],
            ['name' => 'Retained Earnings', 'code' => '3201', 'type' => 'equity', 'normal' => 'credit'],
            ['name' => 'Sales Revenue', 'code' => Account::CODE_SALES_REVENUE, 'type' => 'income', 'normal' => 'credit'],
            ['name' => 'Cost of Goods Sold', 'code' => Account::CODE_COGS, 'type' => 'expense', 'normal' => 'debit'],
            ['name' => 'Operating Expenses', 'code' => '5201', 'type' => 'expense', 'normal' => 'debit'],
        ];

        foreach ($accounts as $account) {
            Account::withTrashed()->updateOrCreate(
                ['code' => $account['code']],
                [
                    'name' => $account['name'],
                    'type' => $account['type'],
                    'account_type' => $account['type'],
                    'normal_balance' => $account['normal'],
                    'is_system' => true,
                    'is_active' => true,
                    'deleted_at' => null,
                ]
            );
        }
    }
}
