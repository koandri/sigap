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
            'maintenance.dashboard.view',
            'maintenance.assets.view',
            'maintenance.assets.manage',
            'maintenance.schedules.view',
            'maintenance.schedules.manage',
            'maintenance.work-orders.view',
            'maintenance.work-orders.create',
            'maintenance.work-orders.complete',
            'maintenance.work-orders.assign',
            'maintenance.work-orders.work',
            'maintenance.work-orders.verify',
            'maintenance.work-orders.close',
            'maintenance.reports.view',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
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
