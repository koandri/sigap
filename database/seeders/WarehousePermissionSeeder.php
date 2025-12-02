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
            ['name' => 'warehouses.dashboard.view', 'description' => 'View warehouse dashboard'],
            
            // Warehouse Management
            ['name' => 'warehouses.view', 'description' => 'View warehouses'],
            ['name' => 'warehouses.create', 'description' => 'Create warehouses'],
            ['name' => 'warehouses.edit', 'description' => 'Edit warehouses'],
            ['name' => 'warehouses.delete', 'description' => 'Delete warehouses'],
            
            // Inventory Management (for warehouse operations)
            ['name' => 'warehouses.inventory.view', 'description' => 'View warehouse inventory'],
            ['name' => 'warehouses.inventory.create', 'description' => 'Create warehouse inventory records'],
            ['name' => 'warehouses.inventory.edit', 'description' => 'Edit warehouse inventory records'],
            ['name' => 'warehouses.inventory.delete', 'description' => 'Delete warehouse inventory records'],
        ];

        foreach ($permissions as $permissionData) {
            Permission::firstOrCreate(
                ['name' => $permissionData['name'], 'guard_name' => 'web'],
                ['description' => $permissionData['description'] ?? null]
            );
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







