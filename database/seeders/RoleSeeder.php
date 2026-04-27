<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        $permissions = [
            'view dashboard',
            'manage products',
            'manage stock',
            'pos checkout',
            'pos orders',
            'manage customers',
            'view sales reports',
            'manage suppliers',
            'manage purchases',
            'manage supplier payments',
            'manage customer payments',
            'view customer ledger',
            'view supplier ledger',
            'view receivables report',
            'view payables report',
            'manage chart of accounts',
            'view journal entries',
            'manage accounts',
            'manage receivables',
            'manage payables',
            'post accounting entries',
            'view general ledger',
            'view balance sheet',
            'view customer statements',
            'view vendor statements',
            'v2 dashboard',
            'v2 purchase book',
            'v2 sale bill book',
            'v2 receipt vouchers',
            'v2 payment vouchers',
            'v2 journal vouchers',
            'v2 assets ledger',
            'v2 receivables ledger',
            'v2 payables ledger',
            'v2 liabilities ledger',
            'v2 capital ledger',
            'v2 expenses ledger',
            'v2 revenue ledger',
            'v2 cash bank ledger',
            'v2 cost visible',
            'v2 stock ledger',
            'v2 stock manager',
            'v2 category manager',
            'v2 brand manager',
            'v2 accounts manager',
            'v2 account details manager',
            'v2 trial balance',
            'v2 trial balance aging',
            'v2 income statement',
            'v2 balance sheet',
            'v2 backup restore',
            'v2 add remove users',
            'v2 send sms function',
            'v2 insert',
            'v2 edit',
            'v2 delete',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate([
                'name' => $permission,
                'guard_name' => 'web',
            ]);
        }

        // Define all roles
        $roles = ['admin', 'individual', 'company'];

        foreach ($roles as $roleName) {
            Role::firstOrCreate([
                'name' => $roleName,
                'guard_name' => 'web',
            ]);
        }

        $adminRole = Role::where('name', 'admin')->first();
        if ($adminRole) {
            $adminRole->syncPermissions($permissions);
        }

        // Optionally create sample users and assign roles

        // Admin user
        $adminUser = User::firstOrCreate(
            ['email' => 'admin@gmail.com'],
            [
                'name' => 'Admin',
                'username' => 'admin',
                'password' => Hash::make('admin@gmail.com'),
            ]
        );
        $adminUser->assignRole('admin');

        // Individual user
        $individualUser = User::firstOrCreate(
            ['email' => 'individual@example.com'],
            [
                'name' => 'John Doe',
                'username' => 'john_individual',
                'password' => Hash::make('password'),
            ]
        );
        $individualUser->assignRole('individual');

        // Company user
        $companyUser = User::firstOrCreate(
            ['email' => 'company@example.com'],
            [
                'name' => 'ACME Corp',
                'username' => 'acme_company',
                'password' => Hash::make('password'),
            ]
        );
        $companyUser->assignRole('company');
    }
}
