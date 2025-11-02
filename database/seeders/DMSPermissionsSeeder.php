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
            // Document permissions
            'dms.documents.view',
            'dms.documents.create',
            'dms.documents.edit',
            'dms.documents.delete',
            
            // Document version permissions
            'dms.versions.create',
            'dms.versions.edit',
            'dms.versions.approve',
            'dms.versions.view',
            
            // Document access permissions
            'dms.access.request',
            'dms.access.approve',
            'dms.access.view',
            
            // Form request permissions
            'dms.forms.request',
            'dms.forms.process',
            'dms.forms.view',
            
            // Admin permissions
            'dms.admin',
            'dms.outgoing_letters.create',
            'dms.internal_memos.create',

            // Reports
            'dms.sla.report.view',
            'dms.reports.view',
            'asset.reports.view',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        // Assign permissions to roles
        $this->assignPermissionsToRoles();
    }

    private function assignPermissionsToRoles(): void
    {
        // Super Admin - All permissions
        $superAdmin = Role::firstOrCreate(['name' => 'Super Admin']);
        $superAdmin->givePermissionTo([
            'dms.documents.view',
            'dms.documents.create',
            'dms.documents.edit',
            'dms.documents.delete',
            'dms.versions.create',
            'dms.versions.edit',
            'dms.versions.approve',
            'dms.versions.view',
            'dms.access.request',
            'dms.access.approve',
            'dms.access.view',
            'dms.forms.request',
            'dms.forms.process',
            'dms.forms.view',
            'dms.admin',
            'dms.outgoing_letters.create',
            'dms.internal_memos.create',
        ]);

        // Owner - All permissions
        $owner = Role::firstOrCreate(['name' => 'Owner']);
        $owner->givePermissionTo([
            'dms.documents.view',
            'dms.documents.create',
            'dms.documents.edit',
            'dms.documents.delete',
            'dms.versions.create',
            'dms.versions.edit',
            'dms.versions.approve',
            'dms.versions.view',
            'dms.access.request',
            'dms.access.approve',
            'dms.access.view',
            'dms.forms.request',
            'dms.forms.process',
            'dms.forms.view',
            'dms.admin',
            'dms.outgoing_letters.create',
            'dms.internal_memos.create',
        ]);

        // Document Control - Process forms and manage documents
        $documentControl = Role::firstOrCreate(['name' => 'Document Control']);
        $documentControl->givePermissionTo([
            'dms.documents.view',
            'dms.documents.create',
            'dms.documents.edit',
            'dms.versions.create',
            'dms.versions.edit',
            'dms.versions.approve',
            'dms.versions.view',
            'dms.access.approve',
            'dms.access.view',
            'dms.forms.process',
            'dms.forms.view',
            'dms.internal_memos.create',
        ]);

        // Manager - Approve documents and manage team access
        $manager = Role::firstOrCreate(['name' => 'Manager']);
        $manager->givePermissionTo([
            'dms.documents.view',
            'dms.documents.create',
            'dms.documents.edit',
            'dms.versions.create',
            'dms.versions.edit',
            'dms.versions.approve',
            'dms.versions.view',
            'dms.access.request',
            'dms.access.approve',
            'dms.access.view',
            'dms.forms.request',
            'dms.forms.view',
            'dms.internal_memos.create',
        ]);

        // Regular users - Basic access
        $user = Role::firstOrCreate(['name' => 'User']);
        $user->givePermissionTo([
            'dms.documents.view',
            'dms.access.request',
            'dms.access.view',
            'dms.forms.request',
            'dms.forms.view',
        ]);
    }
}
