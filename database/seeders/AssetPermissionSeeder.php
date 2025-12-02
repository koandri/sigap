<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

final class AssetPermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create Asset Permissions
        $permissions = [
            // Asset Management
            ['name' => 'assets.view', 'description' => 'View assets in the system'],
            ['name' => 'assets.create', 'description' => 'Create new assets'],
            ['name' => 'assets.update', 'description' => 'Update existing assets'],
            ['name' => 'assets.delete', 'description' => 'Delete assets'],
            
            // Asset Category Management
            ['name' => 'asset-categories.view', 'description' => 'View asset categories'],
            ['name' => 'asset-categories.update', 'description' => 'Update asset categories'],
        ];

        foreach ($permissions as $permissionData) {
            Permission::firstOrCreate(
                ['name' => $permissionData['name'], 'guard_name' => 'web'],
                ['description' => $permissionData['description'] ?? null]
            );
        }

        // Get permission names for role assignment
        $permissionNames = array_column($permissions, 'name');

        // Assign permissions to roles
        $this->assignPermissionsToRoles($permissionNames);

        $this->command->info('Asset permissions created successfully!');
    }

    private function assignPermissionsToRoles(array $permissionNames): void
    {
        // Super Admin - All permissions
        $superAdmin = Role::firstOrCreate(['name' => 'Super Admin']);
        $superAdmin->givePermissionTo($permissionNames);

        // Owner - All permissions
        $owner = Role::firstOrCreate(['name' => 'Owner']);
        $owner->givePermissionTo($permissionNames);

        // IT Staff - Full asset management
        $itStaff = Role::firstOrCreate(['name' => 'IT Staff']);
        $itStaff->givePermissionTo($permissionNames);

        // Engineering - View assets
        $engineering = Role::where('name', 'Engineering')->first();
        if ($engineering) {
            $engineering->givePermissionTo([
                'assets.view',
                'asset-categories.view',
            ]);
        }

        $this->command->info('Asset roles and permissions assigned successfully!');
    }
}

