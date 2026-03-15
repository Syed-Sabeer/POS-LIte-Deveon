<?php

namespace Database\Seeders;

use App\Models\Account;
use Illuminate\Database\Seeder;

class ChartOfAccountsSeeder extends Seeder
{
    public function run(): void
    {
        $accounts = [
            ['name' => 'Cash in Hand', 'code' => Account::CODE_CASH, 'type' => 'asset'],
            ['name' => 'Bank Account', 'code' => Account::CODE_BANK, 'type' => 'asset'],
            ['name' => 'Accounts Receivable', 'code' => Account::CODE_RECEIVABLE, 'type' => 'asset'],
            ['name' => 'Inventory', 'code' => Account::CODE_INVENTORY, 'type' => 'asset'],
            ['name' => 'Accounts Payable', 'code' => Account::CODE_PAYABLE, 'type' => 'liability'],
            ['name' => 'Sales Revenue', 'code' => Account::CODE_SALES_REVENUE, 'type' => 'income'],
            ['name' => 'Cost of Goods Sold', 'code' => Account::CODE_COGS, 'type' => 'expense'],
        ];

        foreach ($accounts as $account) {
            Account::withTrashed()->updateOrCreate(
                ['code' => $account['code']],
                [
                    'name' => $account['name'],
                    'type' => $account['type'],
                    'is_system' => true,
                    'is_active' => true,
                    'deleted_at' => null,
                ]
            );
        }
    }
}
