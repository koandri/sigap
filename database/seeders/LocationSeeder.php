<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Location;
use Illuminate\Database\Seeder;

final class LocationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $locations = [
            [
                'name' => 'Gelam Warehouse',
                'code' => 'GL-WH',
                'is_active' => true,
            ],
            [
                'name' => 'Tanggulangin Production',
                'code' => 'TA-PR',
                'is_active' => true,
            ],
            [
                'name' => 'Bulang Distribution Center',
                'code' => 'BL-DC',
                'is_active' => true,
            ],
            [
                'name' => 'Graha Mas Office',
                'code' => 'GM-OF',
                'is_active' => true,
            ],
            [
                'name' => 'Surabaya Main Office',
                'code' => 'SB-MO',
                'is_active' => true,
            ],
            [
                'name' => 'Waru Logistics Hub',
                'code' => 'WR-LH',
                'is_active' => true,
            ],
            [
                'name' => 'Sidoarjo Service Center',
                'code' => 'SD-SC',
                'is_active' => true,
            ],
            [
                'name' => 'Taman Manufacturing',
                'code' => 'TM-MF',
                'is_active' => true,
            ],
            [
                'name' => 'Candi Storage Facility',
                'code' => 'CD-SF',
                'is_active' => true,
            ],
            [
                'name' => 'Porong Branch Office',
                'code' => 'PR-BO',
                'is_active' => true,
            ],
        ];

        foreach ($locations as $location) {
            Location::create($location);
        }
    }
}
