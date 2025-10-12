<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\BomType;
use Illuminate\Database\Seeder;

final class BomTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $bomTypes = [
            [
                'code' => 'JC-ADN',
                'name' => 'Job Costing - Adonan',
                'description' => 'Tracks raw materials used to produce Adonans (tapioca flour, prawn powder, salt, etc.).',
                'category' => 'job_costing',
                'stage' => 'adonan',
                'is_active' => true,
            ],
            [
                'code' => 'RO-ADN',
                'name' => 'Roll Over - Adonan',
                'description' => 'Tracks how many adonans are produced by using raw materials.',
                'category' => 'roll_over',
                'stage' => 'adonan',
                'is_active' => true,
            ],
            [
                'code' => 'JC-GLD',
                'name' => 'Job Costing - Gelondongan',
                'description' => 'Tracks adonans used to produce gelondongans.',
                'category' => 'job_costing',
                'stage' => 'gelondongan',
                'is_active' => true,
            ],
            [
                'code' => 'RO-GLD',
                'name' => 'Roll Over - Gelondongan',
                'description' => 'Tracks how many gelondongans are produced from adonans.',
                'category' => 'roll_over',
                'stage' => 'gelondongan',
                'is_active' => true,
            ],
            [
                'code' => 'JC-KG',
                'name' => 'Job Costing - Kerupuk Kg',
                'description' => 'Tracks how many gelondongans are used to produce Kerupuk Kg.',
                'category' => 'job_costing',
                'stage' => 'kerupuk_kg',
                'is_active' => true,
            ],
            [
                'code' => 'RO-KG',
                'name' => 'Roll Over - Kerupuk Kg',
                'description' => 'Tracks how many Kerupuk Kg are produced from gelondongans.',
                'category' => 'roll_over',
                'stage' => 'kerupuk_kg',
                'is_active' => true,
            ],
            [
                'code' => 'JC-PCK',
                'name' => 'Job Costing - Packing',
                'description' => 'Tracks how many Kerupuk Kg and Packing Materials used to produce Packed products.',
                'category' => 'job_costing',
                'stage' => 'packing',
                'is_active' => true,
            ],
            [
                'code' => 'RO-PCK',
                'name' => 'Roll Over - Packing',
                'description' => 'Tracks how many Packed products are produced from Kerupuk Kg and Packing Materials.',
                'category' => 'roll_over',
                'stage' => 'packing',
                'is_active' => true,
            ],
        ];

        foreach ($bomTypes as $bomTypeData) {
            BomType::firstOrCreate(
                ['code' => $bomTypeData['code']],
                $bomTypeData
            );
        }

        $this->command->info('BoM Types created successfully!');
        $this->command->info('Created 8 types: JC-ADN, RO-ADN, JC-GLD, RO-GLD, JC-KG, RO-KG, JC-PCK, RO-PCK');
        $this->command->info('- JC-ADN: Raw materials → Adonan');
        $this->command->info('- RO-ADN: Adonan production tracking');
        $this->command->info('- JC-GLD: Adonan → Gelondongan');
        $this->command->info('- RO-GLD: Gelondongan production tracking');
        $this->command->info('- JC-KG: Gelondongan → Kerupuk Kg');
        $this->command->info('- RO-KG: Kerupuk Kg production tracking');
        $this->command->info('- JC-PCK: Kerupuk Kg + Packing materials → Packed products');
        $this->command->info('- RO-PCK: Packed products production tracking');
    }
}
