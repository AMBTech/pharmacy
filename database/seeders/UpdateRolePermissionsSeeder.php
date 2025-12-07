<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\UserRole;

class UpdateRolePermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Update Admin role
        $admin = UserRole::where('name', 'admin')->first();
        if ($admin) {
            $admin->update([
                'description' => 'Full system access with all permissions',
                'permissions' => ['*']
            ]);
        }

        // Update Manager role
        $manager = UserRole::where('name', 'manager')->first();
        if ($manager) {
            $manager->update([
                'description' => 'Manage inventory, purchases, and view reports',
                'permissions' => [
                    'dashboard.view',
                    'purchases.view', 'purchases.create', 'purchases.edit', 'purchases.delete',
                    'suppliers.view', 'suppliers.create', 'suppliers.edit', 'suppliers.delete',
                    'inventory.view', 'inventory.create', 'inventory.edit', 'inventory.delete',
                    'batches.view',
                    'categories.view', 'categories.create', 'categories.edit', 'categories.delete',
                    'sales.view',
                    'reports.view',
                    'settings.view'
                ]
            ]);
        }

        // Update Cashier role
        $cashier = UserRole::where('name', 'cashier')->first();
        if ($cashier) {
            $cashier->update([
                'description' => 'Process sales and view products',
                'permissions' => [
                    'dashboard.view',
                    'pos.view',
                    'inventory.view',
                    'batches.view',
                    'categories.view',
                    'sales.view',
                    'sales.create'
                ]
            ]);
        }

        $this->command->info('Role permissions updated successfully!');
    }
}
