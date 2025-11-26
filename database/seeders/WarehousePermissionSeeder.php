<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

final class WarehousePermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create Warehouse Permissions
        $permissions = [
            // Warehouse Dashboard
            'warehouses.dashboard.view',
            
            // Warehouse Management
            'warehouses.view',
            'warehouses.create',
            'warehouses.edit',
            'warehouses.delete',
            
            // Inventory Management (for warehouse operations)
            'warehouses.inventory.view',
            'warehouses.inventory.create',
            'warehouses.inventory.edit',
            'warehouses.inventory.delete',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web']);
        }

        // Create Warehouse Roles and assign permissions
        $this->createRolePermissions();

        $this->command->info('Warehouse permissions created successfully!');
    }

    private function createRolePermissions(): void
    {
        // Warehouse Role - Warehouse Management
        $warehouseRole = Role::firstOrCreate(['name' => 'Warehouse', 'guard_name' => 'web']);
        $warehouseRole->givePermissionTo([
            'warehouses.dashboard.view',
            'warehouses.view',
            'warehouses.create',
            'warehouses.edit',
            'warehouses.inventory.view',
            'warehouses.inventory.create',
            'warehouses.inventory.edit',
        ]);

        // PPIC Role - also needs warehouse access
        $ppicRole = Role::firstOrCreate(['name' => 'PPIC', 'guard_name' => 'web']);
        $ppicRole->givePermissionTo([
            'warehouses.dashboard.view',
            'warehouses.view',
            'warehouses.create',
            'warehouses.edit',
            'warehouses.inventory.view',
            'warehouses.inventory.create',
            'warehouses.inventory.edit',
        ]);

        // Admin Central Role - Read-only warehouse access
        $adminCentralRole = Role::firstOrCreate(['name' => 'Admin Central', 'guard_name' => 'web']);
        $adminCentralRole->givePermissionTo([
            'warehouses.dashboard.view',
            'warehouses.view',
            'warehouses.inventory.view',
        ]);

        $this->command->info('Warehouse roles and permissions assigned successfully!');
    }
}




