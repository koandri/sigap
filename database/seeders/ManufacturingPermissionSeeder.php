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
            ['name' => 'manufacturing.production-plans.view', 'description' => 'View production plans'],
            ['name' => 'manufacturing.production-plans.create', 'description' => 'Create production plans'],
            ['name' => 'manufacturing.production-plans.edit', 'description' => 'Edit production plans'],
            ['name' => 'manufacturing.production-plans.delete', 'description' => 'Delete production plans'],
            ['name' => 'manufacturing.production-plans.approve', 'description' => 'Approve production plans'],
            ['name' => 'manufacturing.production-plans.start', 'description' => 'Start production plans'],
            ['name' => 'manufacturing.production-plans.record-actuals', 'description' => 'Record actual production data'],
            ['name' => 'manufacturing.production-plans.complete', 'description' => 'Complete production plans'],
            ['name' => 'manufacturing.production-plans.view-actuals', 'description' => 'View actual production data'],
            
            // Yield Guidelines Management
            ['name' => 'manufacturing.yield-guidelines.view', 'description' => 'View yield guidelines'],
            ['name' => 'manufacturing.yield-guidelines.create', 'description' => 'Create yield guidelines'],
            ['name' => 'manufacturing.yield-guidelines.edit', 'description' => 'Edit yield guidelines'],
            ['name' => 'manufacturing.yield-guidelines.delete', 'description' => 'Delete yield guidelines'],

            // Packing Material Blueprints
            ['name' => 'manufacturing.packing-blueprints.view', 'description' => 'View packing material blueprints'],
            ['name' => 'manufacturing.packing-blueprints.create', 'description' => 'Create packing material blueprints'],
            ['name' => 'manufacturing.packing-blueprints.edit', 'description' => 'Edit packing material blueprints'],
            ['name' => 'manufacturing.packing-blueprints.delete', 'description' => 'Delete packing material blueprints'],
            
            // Production Execution
            ['name' => 'manufacturing.production.view', 'description' => 'View production data'],
            ['name' => 'manufacturing.production.record', 'description' => 'Record production data'],
            ['name' => 'manufacturing.production.edit', 'description' => 'Edit production data'],
            ['name' => 'manufacturing.production.day1', 'description' => 'Access Day 1 production (Adonan & Gelondongan)'],
            ['name' => 'manufacturing.production.day2', 'description' => 'Access Day 2 production (Slicing & Drying)'],
            ['name' => 'manufacturing.production.day3', 'description' => 'Access Day 3 production (Sorting & Packing)'],
            
            // Quality Control
            ['name' => 'manufacturing.qc.view', 'description' => 'View quality control data'],
            ['name' => 'manufacturing.qc.record', 'description' => 'Record quality control data'],
            ['name' => 'manufacturing.qc.approve', 'description' => 'Approve quality control results'],
            ['name' => 'manufacturing.qc.reject', 'description' => 'Reject quality control results'],
            
            // Material Usage Recording
            ['name' => 'manufacturing.materials.view', 'description' => 'View material usage data'],
            ['name' => 'manufacturing.materials.record', 'description' => 'Record material usage'],
            ['name' => 'manufacturing.materials.edit', 'description' => 'Edit material usage data'],
            
            // Reporting & Analytics
            ['name' => 'manufacturing.reports.view', 'description' => 'View manufacturing reports'],
            ['name' => 'manufacturing.reports.export', 'description' => 'Export manufacturing reports'],
            ['name' => 'manufacturing.analytics.view', 'description' => 'View manufacturing analytics'],
            
            // Dashboard Access
            ['name' => 'manufacturing.dashboard.view', 'description' => 'View manufacturing dashboard'],
        ];

        foreach ($permissions as $permissionData) {
            Permission::firstOrCreate(
                ['name' => $permissionData['name'], 'guard_name' => 'web'],
                ['description' => $permissionData['description'] ?? null]
            );
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
