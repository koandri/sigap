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
                'address' => 'Jl. Gelam Raya No. 123',
                'city' => 'Sidoarjo',
                'postal_code' => '61234',
                'phone' => '+62-31-8945678',
                'is_active' => true,
            ],
            [
                'name' => 'Tanggulangin Production',
                'code' => 'TA-PR',
                'address' => 'Jl. Tanggulangin Industri No. 45',
                'city' => 'Sidoarjo',
                'postal_code' => '61274',
                'phone' => '+62-31-8945679',
                'is_active' => true,
            ],
            [
                'name' => 'Bulang Distribution Center',
                'code' => 'BL-DC',
                'address' => 'Jl. Bulang Utara No. 88',
                'city' => 'Sidoarjo',
                'postal_code' => '61256',
                'phone' => '+62-31-8945680',
                'is_active' => true,
            ],
            [
                'name' => 'Graha Mas Office',
                'code' => 'GM-OF',
                'address' => 'Jl. Graha Mas Boulevard No. 10',
                'city' => 'Surabaya',
                'postal_code' => '60115',
                'phone' => '+62-31-8945681',
                'is_active' => true,
            ],
            [
                'name' => 'Surabaya Main Office',
                'code' => 'SB-MO',
                'address' => 'Jl. Raya Darmo No. 99',
                'city' => 'Surabaya',
                'postal_code' => '60264',
                'phone' => '+62-31-8945682',
                'is_active' => true,
            ],
            [
                'name' => 'Waru Logistics Hub',
                'code' => 'WR-LH',
                'address' => 'Jl. Waru Industri No. 56',
                'city' => 'Sidoarjo',
                'postal_code' => '61256',
                'phone' => '+62-31-8945683',
                'is_active' => true,
            ],
            [
                'name' => 'Sidoarjo Service Center',
                'code' => 'SD-SC',
                'address' => 'Jl. Ahmad Yani No. 234',
                'city' => 'Sidoarjo',
                'postal_code' => '61213',
                'phone' => '+62-31-8945684',
                'is_active' => true,
            ],
            [
                'name' => 'Taman Manufacturing',
                'code' => 'TM-MF',
                'address' => 'Jl. Taman Industri No. 12',
                'city' => 'Sidoarjo',
                'postal_code' => '61257',
                'phone' => '+62-31-8945685',
                'is_active' => true,
            ],
            [
                'name' => 'Candi Storage Facility',
                'code' => 'CD-SF',
                'address' => 'Jl. Candi Raya No. 77',
                'city' => 'Sidoarjo',
                'postal_code' => '61271',
                'phone' => '+62-31-8945686',
                'is_active' => true,
            ],
            [
                'name' => 'Porong Branch Office',
                'code' => 'PR-BO',
                'address' => 'Jl. Porong Indah No. 33',
                'city' => 'Sidoarjo',
                'postal_code' => '61274',
                'phone' => '+62-31-8945687',
                'is_active' => true,
            ],
        ];

        foreach ($locations as $location) {
            Location::create($location);
        }
    }
}
