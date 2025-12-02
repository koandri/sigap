<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

final class DMSPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        // Create DMS permissions
        $permissions = [
            // Dashboard permission
            ['name' => 'dms.dashboard.view', 'description' => 'View DMS dashboard'],
            
            // Document permissions
            ['name' => 'dms.documents.view', 'description' => 'View documents in the DMS system'],
            ['name' => 'dms.documents.create', 'description' => 'Create new documents'],
            ['name' => 'dms.documents.edit', 'description' => 'Edit existing documents'],
            ['name' => 'dms.documents.delete', 'description' => 'Delete documents'],
            
            // Document version permissions
            ['name' => 'dms.versions.create', 'description' => 'Create new document versions'],
            ['name' => 'dms.versions.edit', 'description' => 'Edit document versions'],
            ['name' => 'dms.versions.approve', 'description' => 'Approve document versions'],
            ['name' => 'dms.versions.view', 'description' => 'View document versions'],
            
            // Document instance permissions (filled-in templates like outgoing letters, internal memos)
            ['name' => 'dms.instances.view', 'description' => 'View document instances (filled-in templates)'],
            ['name' => 'dms.instances.create', 'description' => 'Create document instances'],
            ['name' => 'dms.instances.edit', 'description' => 'Edit document instances'],
            ['name' => 'dms.instances.delete', 'description' => 'Delete document instances'],
            ['name' => 'dms.instances.approve', 'description' => 'Approve document instances'],
            
            // Document access permissions
            ['name' => 'dms.access.request', 'description' => 'Request access to restricted documents'],
            ['name' => 'dms.access.approve', 'description' => 'Approve document access requests'],
            ['name' => 'dms.access.view', 'description' => 'View document access requests'],
            
            // Form request permissions
            ['name' => 'dms.forms.request', 'description' => 'Request printed forms'],
            ['name' => 'dms.forms.process', 'description' => 'Process form requests'],
            ['name' => 'dms.forms.view', 'description' => 'View form requests'],
            
            // Admin permissions
            ['name' => 'dms.admin', 'description' => 'Full administrative access to DMS system'],
            ['name' => 'dms.outgoing_letters.create', 'description' => 'Create outgoing letters'],
            ['name' => 'dms.internal_memos.create', 'description' => 'Create internal memos'],

            // Reports
            ['name' => 'dms.sla.report.view', 'description' => 'View SLA reports'],
            ['name' => 'dms.reports.view', 'description' => 'View DMS reports'],

            // Document borrowing permissions
            ['name' => 'dms.borrows.request', 'description' => 'Request to borrow documents'],
            ['name' => 'dms.borrows.approve', 'description' => 'Approve document borrowing requests'],
            ['name' => 'dms.borrows.manage', 'description' => 'Manage document borrowing (full control)'],
            ['name' => 'dms.borrows.view', 'description' => 'View document borrowing requests'],
        ];

        foreach ($permissions as $permissionData) {
            Permission::firstOrCreate(
                ['name' => $permissionData['name']],
                ['description' => $permissionData['description'] ?? null]
            );
        }

        // Get permission names for role assignment
        $permissionNames = array_column($permissions, 'name');

        // Assign permissions to roles
        $this->assignPermissionsToRoles($permissionNames);
    }

    private function assignPermissionsToRoles(array $permissionNames): void
    {

        // Super Admin - All permissions
        $superAdmin = Role::firstOrCreate(['name' => 'Super Admin']);
        $superAdmin->givePermissionTo($permissionNames);

        // Owner - All permissions
        $owner = Role::firstOrCreate(['name' => 'Owner']);
        $owner->givePermissionTo($permissionNames);

        // Document Control - Process forms and manage documents
        $documentControl = Role::firstOrCreate(['name' => 'Document Control']);
        $documentControl->givePermissionTo([
            'dms.dashboard.view',
            'dms.documents.view',
            'dms.documents.create',
            'dms.documents.edit',
            'dms.versions.create',
            'dms.versions.edit',
            'dms.versions.approve',
            'dms.versions.view',
            'dms.instances.view',
            'dms.instances.create',
            'dms.instances.edit',
            'dms.instances.approve',
            'dms.access.approve',
            'dms.access.view',
            'dms.forms.process',
            'dms.forms.view',
            'dms.internal_memos.create',
            'dms.borrows.manage',
            'dms.borrows.view',
        ]);

        // Manager - Approve documents and manage team access
        $manager = Role::firstOrCreate(['name' => 'Manager']);
        $manager->givePermissionTo([
            'dms.dashboard.view',
            'dms.documents.view',
            'dms.documents.create',
            'dms.documents.edit',
            'dms.versions.create',
            'dms.versions.edit',
            'dms.versions.approve',
            'dms.versions.view',
            'dms.instances.view',
            'dms.instances.create',
            'dms.instances.edit',
            'dms.instances.approve',
            'dms.access.request',
            'dms.access.approve',
            'dms.access.view',
            'dms.forms.request',
            'dms.forms.view',
            'dms.internal_memos.create',
            'dms.borrows.request',
            'dms.borrows.view',
        ]);

        // Regular users - Basic access
        $user = Role::firstOrCreate(['name' => 'User']);
        $user->givePermissionTo([
            'dms.dashboard.view',
            'dms.documents.view',
            'dms.instances.view',
            'dms.instances.create',
            'dms.access.request',
            'dms.access.view',
            'dms.forms.request',
            'dms.forms.view',
            'dms.borrows.request',
            'dms.borrows.view',
        ]);
    }
}
