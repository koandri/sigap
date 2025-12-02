<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

final class OptionsPermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create Options Permissions for Items and Item Categories
        $permissions = [
            // Item Management  
            ['name' => 'options.items.view', 'description' => 'View items'],
            ['name' => 'options.items.edit', 'description' => 'Edit items'],
            ['name' => 'options.items.delete', 'description' => 'Delete items'],
            ['name' => 'options.items.import', 'description' => 'Import items'],
            
            // Item Categories
            ['name' => 'options.item-categories.view', 'description' => 'View item categories'],
            ['name' => 'options.item-categories.create', 'description' => 'Create item categories'],
            ['name' => 'options.item-categories.edit', 'description' => 'Edit item categories'],
            ['name' => 'options.item-categories.delete', 'description' => 'Delete item categories'],
        ];

        foreach ($permissions as $permissionData) {
            Permission::firstOrCreate(
                ['name' => $permissionData['name'], 'guard_name' => 'web'],
                ['description' => $permissionData['description'] ?? null]
            );
        }

        // Assign permissions to roles
        $this->assignRolePermissions();

        $this->command->info('Options permissions created successfully!');
    }

    private function assignRolePermissions(): void
    {
        // PPIC Role - needs item management access
        $ppicRole = Role::firstOrCreate(['name' => 'PPIC', 'guard_name' => 'web']);
        $ppicRole->givePermissionTo([
            'options.items.view',
            'options.items.edit',
            'options.items.import',
            'options.item-categories.view',
            'options.item-categories.create',
            'options.item-categories.edit',
        ]);

        // RnD Role - needs item view access
        $rndRole = Role::firstOrCreate(['name' => 'RnD', 'guard_name' => 'web']);
        $rndRole->givePermissionTo([
            'options.items.view',
            'options.item-categories.view',
        ]);

        // Production Role - needs item view access
        $productionRole = Role::firstOrCreate(['name' => 'Production', 'guard_name' => 'web']);
        $productionRole->givePermissionTo([
            'options.items.view',
            'options.item-categories.view',
        ]);

        // QC Role - needs item view access
        $qcRole = Role::firstOrCreate(['name' => 'QC', 'guard_name' => 'web']);
        $qcRole->givePermissionTo([
            'options.items.view',
            'options.item-categories.view',
        ]);

        // Warehouse Role - needs item management access
        $warehouseRole = Role::firstOrCreate(['name' => 'Warehouse', 'guard_name' => 'web']);
        $warehouseRole->givePermissionTo([
            'options.items.view',
            'options.item-categories.view',
        ]);

        // Admin Central Role - Read-only access
        $adminCentralRole = Role::firstOrCreate(['name' => 'Admin Central', 'guard_name' => 'web']);
        $adminCentralRole->givePermissionTo([
            'options.items.view',
            'options.item-categories.view',
        ]);

        $this->command->info('Options roles and permissions assigned successfully!');
    }
}







