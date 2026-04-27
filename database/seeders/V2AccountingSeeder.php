<?php

namespace Database\Seeders;

use App\Models\V2\Account;
use App\Models\V2\Brand;
use App\Models\V2\Category;
use App\Models\V2\Setting;
use Illuminate\Database\Seeder;

class V2AccountingSeeder extends Seeder
{
    public function run(): void
    {
        $unknownCategory = Category::withTrashed()->updateOrCreate(
            ['name' => 'Unknown'],
            ['is_active' => true, 'deleted_at' => null]
        );

        Brand::withTrashed()->updateOrCreate(
            ['name' => 'Unknown'],
            ['is_active' => true, 'deleted_at' => null]
        );

        $accounts = [
            ['type' => Account::TYPE_CASH_BANK, 'code' => 'CBK00001', 'name' => 'Cash in Hand'],
            ['type' => Account::TYPE_CASH_BANK, 'code' => 'CBK00002', 'name' => 'Bank Account'],
            ['type' => Account::TYPE_RECEIVABLE, 'code' => 'ACR00001', 'name' => 'Walk-in Customer'],
            ['type' => Account::TYPE_PAYABLE, 'code' => 'ACP00001', 'name' => 'General Supplier'],
            ['type' => Account::TYPE_ASSET, 'code' => 'AST00001', 'name' => 'Inventory Account'],
            ['type' => Account::TYPE_REVENUE, 'code' => 'REV00001', 'name' => 'Sales Account'],
            ['type' => Account::TYPE_EXPENSE, 'code' => 'EXP00001', 'name' => 'Purchase Account'],
            ['type' => Account::TYPE_EXPENSE, 'code' => 'EXP00002', 'name' => 'Discount Account'],
            ['type' => Account::TYPE_EXPENSE, 'code' => 'EXP00003', 'name' => 'Cost of Goods Sold'],
            ['type' => Account::TYPE_CAPITAL, 'code' => 'CAP00001', 'name' => 'Owner Capital'],
        ];

        foreach ($accounts as $account) {
            Account::withTrashed()->updateOrCreate(
                ['code' => $account['code']],
                [
                    'account_type' => $account['type'],
                    'name' => $account['name'],
                    'opening_date' => now()->toDateString(),
                    'opening_amount' => 0,
                    'currency_rate' => 1,
                    'is_system' => true,
                    'is_active' => true,
                    'deleted_at' => null,
                ]
            );
        }

        Setting::updateOrCreate(['key' => 'default_category_id'], ['value' => (string) $unknownCategory->id]);
        Setting::updateOrCreate(['key' => 'company_name'], ['value' => config('app.name', 'Company')]);
        Setting::updateOrCreate(['key' => 'company_address'], ['value' => '']);
        Setting::updateOrCreate(['key' => 'company_contact'], ['value' => '']);
    }
}
