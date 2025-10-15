<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\MaintenanceType;

class MaintenanceTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $maintenanceTypes = [
            [
                'name' => 'Preventive Maintenance',
                'code' => 'PREV',
                'description' => 'Scheduled maintenance to prevent equipment failure',
                'color' => '#28a745',
                'is_active' => true,
            ],
            [
                'name' => 'Corrective Maintenance',
                'code' => 'CORR',
                'description' => 'Repair work to restore equipment to working condition',
                'color' => '#fd7e14',
                'is_active' => true,
            ],
            [
                'name' => 'Emergency Repair',
                'code' => 'EMRG',
                'description' => 'Urgent repairs for critical equipment failures',
                'color' => '#dc3545',
                'is_active' => true,
            ],
            [
                'name' => 'Inspection',
                'code' => 'INSP',
                'description' => 'Regular inspections to assess equipment condition',
                'color' => '#007bff',
                'is_active' => true,
            ],
            [
                'name' => 'Calibration',
                'code' => 'CALI',
                'description' => 'Calibration of measuring instruments and equipment',
                'color' => '#6f42c1',
                'is_active' => true,
            ],
            [
                'name' => 'Enhancement',
                'code' => 'ENH',
                'description' => 'Equipment modifications and improvements',
                'color' => '#17a2b8',
                'is_active' => true,
            ],
        ];

        foreach ($maintenanceTypes as $type) {
            MaintenanceType::firstOrCreate(['code' => $type['code']], $type);
        }
    }
}
