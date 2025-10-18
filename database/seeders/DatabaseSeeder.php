<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            UserSeeder::class,
            ManufacturingPermissionSeeder::class,
            MaintenancePermissionSeeder::class,
            FacilityPermissionSeeder::class,
            LocationSeeder::class,
            AssetCategorySeeder::class,
            MaintenanceTypeSeeder::class,
            BomTypeSeeder::class,
            WarehouseShelfSeeder::class,
            ShelfPositionSeeder::class,
            AssetSeeder::class,
        ]);
    }
}
