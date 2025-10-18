<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Asset;
use App\Models\AssetCategory;
use App\Models\Department;
use App\Models\Location;
use App\Models\User;
use Illuminate\Database\Seeder;

final class AssetSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get all active locations, categories, departments, and users
        $locations = Location::where('is_active', true)->pluck('id')->toArray();
        $categories = AssetCategory::where('is_active', true)->pluck('id')->toArray();
        $departments = Department::pluck('id')->toArray();
        $users = User::pluck('id')->toArray();

        // Asset statuses (must match ENUM in database)
        $statuses = ['operational', 'down', 'maintenance', 'disposed'];

        // Sample manufacturers
        $manufacturers = [
            'Bosch', 'Siemens', 'GE', 'Mitsubishi', 'Schneider Electric',
            'ABB', 'Honeywell', 'Rockwell Automation', 'Festo', 'SMC',
            'Parker Hannifin', 'Omron', 'Fanuc', 'Yaskawa', 'Emerson'
        ];

        // Create 100 assets
        for ($i = 1; $i <= 100; $i++) {
            $purchaseDate = fake()->dateTimeBetween('-5 years', '-1 month');
            $warrantyMonths = fake()->numberBetween(12, 60);
            $status = fake()->randomElement($statuses);
            $isActive = $status !== 'disposed';

            $assetData = [
                'name' => fake()->words(3, true) . ' Asset #' . $i,
                'code' => 'AST-' . str_pad((string)$i, 5, '0', STR_PAD_LEFT),
                'asset_category_id' => fake()->randomElement($categories),
                'location_id' => fake()->randomElement($locations),
                'purchase_date' => $purchaseDate,
                'warranty_expiry' => (clone $purchaseDate)->modify("+{$warrantyMonths} months"),
                'serial_number' => strtoupper(fake()->bothify('??-####-????')),
                'manufacturer' => fake()->randomElement($manufacturers),
                'model' => strtoupper(fake()->bothify('??-###?')),
                'status' => $status,
                'specifications' => [
                    'voltage' => fake()->randomElement(['110V', '220V', '380V', '440V']),
                    'power' => fake()->numberBetween(500, 50000) . 'W',
                    'weight' => fake()->numberBetween(10, 500) . 'kg',
                    'dimensions' => fake()->numberBetween(50, 200) . 'x' . fake()->numberBetween(50, 200) . 'x' . fake()->numberBetween(50, 200) . 'cm',
                ],
                'is_active' => $isActive,
            ];

            // Assign department and user randomly (60% chance)
            if (fake()->boolean(60) && !empty($departments)) {
                $assetData['department_id'] = fake()->randomElement($departments);
            }

            if (fake()->boolean(60) && !empty($users)) {
                $assetData['user_id'] = fake()->randomElement($users);
            }

            // If asset is disposed, add disposal details
            if ($status === 'disposed') {
                $disposedDate = fake()->dateTimeBetween($purchaseDate, 'now');
                $assetData['disposed_date'] = $disposedDate;
                $assetData['disposal_reason'] = fake()->randomElement([
                    'End of life',
                    'Beyond repair',
                    'Replaced by newer model',
                    'Safety concerns',
                    'No longer needed',
                ]);
                if (!empty($users)) {
                    $assetData['disposed_by'] = fake()->randomElement($users);
                }
            }

            Asset::create($assetData);
        }

        $this->command->info('Created 100 assets successfully!');
    }
}

