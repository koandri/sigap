<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;

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
            'maintenance.reports.view',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }
    }
}
