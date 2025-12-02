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
            ['name' => 'facility.dashboard.view', 'description' => 'View facility management dashboard'],
            ['name' => 'facility.schedules.view', 'description' => 'View cleaning schedules'],
            ['name' => 'facility.schedules.create', 'description' => 'Create cleaning schedules'],
            ['name' => 'facility.schedules.edit', 'description' => 'Edit cleaning schedules'],
            ['name' => 'facility.schedules.delete', 'description' => 'Delete cleaning schedules'],
            ['name' => 'facility.tasks.view', 'description' => 'View cleaning tasks'],
            ['name' => 'facility.tasks.assign', 'description' => 'Assign cleaning tasks to staff'],
            ['name' => 'facility.tasks.complete', 'description' => 'Mark cleaning tasks as complete'],
            ['name' => 'facility.tasks.bulk-assign', 'description' => 'Bulk assign cleaning tasks'],
            ['name' => 'facility.submissions.review', 'description' => 'Review cleaning submissions'],
            ['name' => 'facility.submissions.approve', 'description' => 'Approve cleaning submissions'],
            ['name' => 'facility.requests.view', 'description' => 'View facility requests'],
            ['name' => 'facility.requests.handle', 'description' => 'Handle facility requests'],
            ['name' => 'facility.reports.view', 'description' => 'View facility reports'],
            ['name' => 'facility.alerts.resolve', 'description' => 'Resolve facility alerts'],
        ];

        foreach ($permissions as $permissionData) {
            Permission::firstOrCreate(
                ['name' => $permissionData['name']],
                ['description' => $permissionData['description'] ?? null]
            );
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

        // Get permission names for role assignment
        $permissionNames = array_column($permissions, 'name');

        // Super Admin and Owner should have all permissions
        $superAdmin = Role::where('name', 'Super Admin')->first();
        $owner = Role::where('name', 'Owner')->first();

        if ($superAdmin) {
            $superAdmin->givePermissionTo($permissionNames);
        }

        if ($owner) {
            $owner->givePermissionTo($permissionNames);
        }
    }
}
