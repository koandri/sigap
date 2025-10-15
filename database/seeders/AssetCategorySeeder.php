<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\AssetCategory;

class AssetCategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            [
                'name' => 'Production Equipment',
                'code' => 'PROD',
                'description' => 'Machinery and equipment used in production processes',
                'is_active' => true,
            ],
            [
                'name' => 'Processing Machinery',
                'code' => 'PROC',
                'description' => 'Equipment for food processing and manufacturing',
                'is_active' => true,
            ],
            [
                'name' => 'Refrigeration Systems',
                'code' => 'REFR',
                'description' => 'Cooling and refrigeration equipment',
                'is_active' => true,
            ],
            [
                'name' => 'Packaging Equipment',
                'code' => 'PACK',
                'description' => 'Machinery for packaging and labeling products',
                'is_active' => true,
            ],
            [
                'name' => 'Facility Infrastructure',
                'code' => 'FACL',
                'description' => 'Building systems and infrastructure equipment',
                'is_active' => true,
            ],
        ];

        foreach ($categories as $category) {
            AssetCategory::create($category);
        }
    }
}
