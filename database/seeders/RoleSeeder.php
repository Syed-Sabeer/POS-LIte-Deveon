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
