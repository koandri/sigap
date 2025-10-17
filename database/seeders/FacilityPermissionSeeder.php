<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class FacilityPermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $permissions = [
            'facility.dashboard.view',
            'facility.schedules.view',
            'facility.schedules.create',
            'facility.schedules.edit',
            'facility.schedules.delete',
            'facility.tasks.view',
            'facility.tasks.assign',
            'facility.tasks.complete',
            'facility.tasks.bulk-assign',
            'facility.submissions.review',
            'facility.submissions.approve',
            'facility.requests.view',
            'facility.requests.handle',
            'facility.reports.view',
            'facility.alerts.resolve',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        // Create Cleaner role
        $cleaner = Role::firstOrCreate([
            'name' => 'Cleaner',
            'guard_name' => 'web'
        ]);

        // Create General Affairs role
        $generalAffairs = Role::firstOrCreate([
            'name' => 'General Affairs',
            'guard_name' => 'web'
        ]);

        // Assign permissions to Cleaner
        $cleaner->syncPermissions([
            'facility.tasks.view',
            'facility.tasks.complete',
        ]);

        // Assign permissions to General Affairs
        $generalAffairs->syncPermissions([
            'facility.dashboard.view',
            'facility.schedules.view',
            'facility.schedules.create',
            'facility.schedules.edit',
            'facility.schedules.delete',
            'facility.tasks.view',
            'facility.tasks.assign',
            'facility.tasks.bulk-assign',
            'facility.submissions.review',
            'facility.submissions.approve',
            'facility.requests.view',
            'facility.requests.handle',
            'facility.reports.view',
            'facility.alerts.resolve',
        ]);

        // Super Admin and Owner should have all permissions
        $superAdmin = Role::where('name', 'Super Admin')->first();
        $owner = Role::where('name', 'Owner')->first();

        if ($superAdmin) {
            $superAdmin->givePermissionTo($permissions);
        }

        if ($owner) {
            $owner->givePermissionTo($permissions);
        }
    }
}
