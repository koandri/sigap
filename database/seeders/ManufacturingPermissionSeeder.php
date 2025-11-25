<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

final class ManufacturingPermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create Manufacturing Permissions (Production-focused only)
        $permissions = [
            // Production Planning
            'manufacturing.production-plans.view',
            'manufacturing.production-plans.create',
            'manufacturing.production-plans.edit',
            'manufacturing.production-plans.delete',
            'manufacturing.production-plans.approve',
            'manufacturing.production-plans.start',
            'manufacturing.production-plans.record-actuals',
            'manufacturing.production-plans.complete',
            'manufacturing.production-plans.view-actuals',
            
            // Yield Guidelines Management
            'manufacturing.yield-guidelines.view',
            'manufacturing.yield-guidelines.create',
            'manufacturing.yield-guidelines.edit',
            'manufacturing.yield-guidelines.delete',

            // Packing Material Blueprints
            'manufacturing.packing-blueprints.view',
            'manufacturing.packing-blueprints.create',
            'manufacturing.packing-blueprints.edit',
            'manufacturing.packing-blueprints.delete',
            
            // Production Execution
            'manufacturing.production.view',
            'manufacturing.production.record',
            'manufacturing.production.edit',
            'manufacturing.production.day1', // Adonan & Gelondongan
            'manufacturing.production.day2', // Slicing & Drying
            'manufacturing.production.day3', // Sorting & Packing
            
            // Quality Control
            'manufacturing.qc.view',
            'manufacturing.qc.record',
            'manufacturing.qc.approve',
            'manufacturing.qc.reject',
            
            // Material Usage Recording
            'manufacturing.materials.view',
            'manufacturing.materials.record',
            'manufacturing.materials.edit',
            
            // Reporting & Analytics
            'manufacturing.reports.view',
            'manufacturing.reports.export',
            'manufacturing.analytics.view',
            
            // Dashboard Access
            'manufacturing.dashboard.view',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web']);
        }

        // Create Manufacturing Roles and assign permissions
        $this->createRolePermissions();

        $this->command->info('Manufacturing permissions created successfully!');
    }

    private function createRolePermissions(): void
    {
        // PPIC Role - Production Planning and Inventory Control
        $ppicRole = Role::firstOrCreate(['name' => 'PPIC', 'guard_name' => 'web']);
        $ppicRole->givePermissionTo([
            'manufacturing.dashboard.view',
            'manufacturing.production-plans.view',
            'manufacturing.production-plans.create',
            'manufacturing.production-plans.edit',
            'manufacturing.production-plans.approve',
            'manufacturing.production-plans.start',
            'manufacturing.production-plans.record-actuals',
            'manufacturing.production-plans.complete',
            'manufacturing.production-plans.view-actuals',
            'manufacturing.yield-guidelines.view',
            'manufacturing.yield-guidelines.create',
            'manufacturing.yield-guidelines.edit',
            'manufacturing.packing-blueprints.view',
            'manufacturing.packing-blueprints.create',
            'manufacturing.packing-blueprints.edit',
            'manufacturing.packing-blueprints.delete',
            'manufacturing.production.view',
            'manufacturing.production.record',
            'manufacturing.production-plans.view-actuals',
            'manufacturing.production-plans.record-actuals',
            'manufacturing.reports.view',
            'manufacturing.reports.export',
            'manufacturing.analytics.view',
        ]);

        // RnD Role - Research and Development  
        $rndRole = Role::firstOrCreate(['name' => 'RnD', 'guard_name' => 'web']);
        $rndRole->givePermissionTo([
            'manufacturing.dashboard.view',
            'manufacturing.materials.view',
            'manufacturing.materials.record',
            'manufacturing.materials.edit',
            'manufacturing.production.view',
            'manufacturing.production.day1',
            'manufacturing.reports.view',
        ]);

        // Production Role - Production Department
        $productionRole = Role::firstOrCreate(['name' => 'Production', 'guard_name' => 'web']);
        $productionRole->givePermissionTo([
            'manufacturing.dashboard.view',
            'manufacturing.production.view',
            'manufacturing.production.record',
            'manufacturing.production.day1',
            'manufacturing.production.day2',
            'manufacturing.production.day3',
            'manufacturing.materials.view',
            'manufacturing.reports.view',
        ]);

        // QC Role - Quality Control
        $qcRole = Role::firstOrCreate(['name' => 'QC', 'guard_name' => 'web']);
        $qcRole->givePermissionTo([
            'manufacturing.dashboard.view',
            'manufacturing.production.view',
            'manufacturing.qc.view',
            'manufacturing.qc.record',
            'manufacturing.qc.approve',
            'manufacturing.qc.reject',
            'manufacturing.reports.view',
        ]);

        // Admin Central Role - Read-only for accounting integration
        $adminCentralRole = Role::firstOrCreate(['name' => 'Admin Central', 'guard_name' => 'web']);
        $adminCentralRole->givePermissionTo([
            'manufacturing.dashboard.view',
            'manufacturing.production-plans.view',
            'manufacturing.yield-guidelines.view',
            'manufacturing.packing-blueprints.view',
            'manufacturing.production.view',
            'manufacturing.materials.view',
            'manufacturing.qc.view',
            'manufacturing.reports.view',
            'manufacturing.reports.export',
            'manufacturing.analytics.view',
        ]);

        $this->command->info('Manufacturing roles and permissions assigned successfully!');
    }
}
