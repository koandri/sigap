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
        // Create Options Permissions for Items, Item Categories, Assets, Users, Roles, Permissions, and Departments
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
            
            // Asset Management (moved from AssetPermissionSeeder)
            ['name' => 'options.assets.view', 'description' => 'View assets in the system'],
            ['name' => 'options.assets.create', 'description' => 'Create new assets'],
            ['name' => 'options.assets.update', 'description' => 'Update existing assets'],
            ['name' => 'options.assets.delete', 'description' => 'Delete assets'],
            
            // Asset Category Management (moved from AssetPermissionSeeder)
            ['name' => 'options.asset-categories.view', 'description' => 'View asset categories'],
            ['name' => 'options.asset-categories.update', 'description' => 'Update asset categories'],
            
            // Asset Reports
            ['name' => 'options.asset-reports.view', 'description' => 'View asset reports'],
            
            // User Management
            ['name' => 'options.users.view', 'description' => 'View users'],
            ['name' => 'options.users.create', 'description' => 'Create new users'],
            ['name' => 'options.users.edit', 'description' => 'Edit users'],
            ['name' => 'options.users.delete', 'description' => 'Delete users'],
            
            // Role Management
            ['name' => 'options.roles.view', 'description' => 'View roles'],
            ['name' => 'options.roles.create', 'description' => 'Create new roles'],
            ['name' => 'options.roles.edit', 'description' => 'Edit roles'],
            ['name' => 'options.roles.delete', 'description' => 'Delete roles'],
            
            // Permission Management
            ['name' => 'options.permissions.view', 'description' => 'View permissions'],
            ['name' => 'options.permissions.create', 'description' => 'Create new permissions'],
            ['name' => 'options.permissions.edit', 'description' => 'Edit permissions'],
            ['name' => 'options.permissions.delete', 'description' => 'Delete permissions'],
            
            // Department Management
            ['name' => 'options.departments.view', 'description' => 'View departments'],
            ['name' => 'options.departments.create', 'description' => 'Create new departments'],
            ['name' => 'options.departments.edit', 'description' => 'Edit departments'],
            ['name' => 'options.departments.delete', 'description' => 'Delete departments'],
            
            // Location Management
            ['name' => 'options.locations.view', 'description' => 'View locations'],
            ['name' => 'options.locations.create', 'description' => 'Create new locations'],
            ['name' => 'options.locations.edit', 'description' => 'Edit locations'],
            ['name' => 'options.locations.delete', 'description' => 'Delete locations'],
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

        // IT Staff Role - Full access to assets, users, roles, permissions, and departments
        $itStaffRole = Role::firstOrCreate(['name' => 'IT Staff', 'guard_name' => 'web']);
        
        // Reset permission cache
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
        
        $itStaffPermissions = [
            // Asset permissions (full access)
            'options.assets.view',
            'options.assets.create',
            'options.assets.update',
            'options.assets.delete',
            'options.asset-categories.view',
            'options.asset-categories.update',
            'options.asset-reports.view',
            
            // User permissions (view and edit, but restrictions will be applied in controller)
            'options.users.view',
            'options.users.edit',
            
            // Role permissions (view and edit, but restrictions will be applied in controller)
            'options.roles.view',
            'options.roles.edit',
            
            // Permission permissions (full access)
            'options.permissions.view',
            'options.permissions.create',
            'options.permissions.edit',
            'options.permissions.delete',
            
            // Department permissions (full access)
            'options.departments.view',
            'options.departments.create',
            'options.departments.edit',
            'options.departments.delete',
            
            // Location permissions (full access)
            'options.locations.view',
            'options.locations.create',
            'options.locations.edit',
            'options.locations.delete',
        ];
        
        // Sync permissions (this will remove old ones and add new ones)
        $itStaffRole->syncPermissions($itStaffPermissions);

        // Engineering Role - needs asset view access
        $engineeringRole = Role::where('name', 'Engineering')->first();
        if ($engineeringRole) {
            $engineeringRole->givePermissionTo([
                'options.assets.view',
                'options.asset-categories.view',
                'options.asset-reports.view',
                'options.locations.view',
            ]);
        }

        $this->command->info('Options roles and permissions assigned successfully!');
    }
}







