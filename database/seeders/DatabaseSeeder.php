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
            DMSPermissionsSeeder::class,
            ManufacturingPermissionSeeder::class,
            WarehousePermissionSeeder::class,
            OptionsPermissionSeeder::class,
            MaintenancePermissionSeeder::class,
            FacilityPermissionSeeder::class,
            MaintenanceTypeSeeder::class,
            WarehouseShelfSeeder::class,
            ShelfPositionSeeder::class,
        ]);
    }
}
