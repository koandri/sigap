<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class MaintenancePermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $permissions = [
            ['name' => 'maintenance.dashboard.view', 'description' => 'View maintenance dashboard'],
            ['name' => 'maintenance.assets.view', 'description' => 'View maintenance assets'],
            ['name' => 'maintenance.assets.manage', 'description' => 'Manage maintenance assets'],
            ['name' => 'maintenance.schedules.view', 'description' => 'View maintenance schedules'],
            ['name' => 'maintenance.schedules.manage', 'description' => 'Manage maintenance schedules'],
            ['name' => 'maintenance.work-orders.view', 'description' => 'View work orders'],
            ['name' => 'maintenance.work-orders.create', 'description' => 'Create work orders'],
            ['name' => 'maintenance.work-orders.complete', 'description' => 'Complete work orders'],
            ['name' => 'maintenance.work-orders.assign', 'description' => 'Assign work orders'],
            ['name' => 'maintenance.work-orders.work', 'description' => 'Work on assigned work orders'],
            ['name' => 'maintenance.work-orders.verify', 'description' => 'Verify completed work orders'],
            ['name' => 'maintenance.work-orders.close', 'description' => 'Close work orders'],
            ['name' => 'maintenance.reports.view', 'description' => 'View maintenance reports'],
        ];

        foreach ($permissions as $permissionData) {
            Permission::firstOrCreate(
                ['name' => $permissionData['name']],
                ['description' => $permissionData['description'] ?? null]
            );
        }

        // Create Engineering roles
        $engineering = Role::firstOrCreate([
            'name' => 'Engineering',
            'guard_name' => 'web'
        ]);

        $engineeringOperator = Role::firstOrCreate([
            'name' => 'Engineering Operator',
            'guard_name' => 'web'
        ]);

        // Assign permissions to Engineering
        $engineering->syncPermissions([
            'maintenance.dashboard.view',
            'maintenance.assets.view',
            'maintenance.assets.manage',
            'maintenance.work-orders.view',
            'maintenance.work-orders.create',
            'maintenance.work-orders.assign',
            'maintenance.work-orders.verify',
            'maintenance.reports.view',
        ]);

        // Assign permissions to Engineering Operator
        $engineeringOperator->syncPermissions([
            'maintenance.work-orders.view',
            'maintenance.work-orders.work',
            'maintenance.assets.view',
        ]);
    }
}
