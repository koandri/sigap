<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Asset;
use App\Models\AssetCategory;
use App\Models\Department;
use App\Models\Location;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Asset>
 */
final class AssetFactory extends Factory
{
    protected $model = Asset::class;

    public function definition(): array
    {
        return [
            'name' => fake()->words(3, true),
            'code' => strtoupper(fake()->unique()->bothify('AST-######')),
            'asset_category_id' => AssetCategory::factory(),
            'location_id' => Location::factory(),
            'purchase_date' => fake()->dateTimeBetween('-2 years', 'now'),
            'warranty_expiry' => fake()->optional()->dateTimeBetween('now', '+2 years'),
            'serial_number' => fake()->optional()->bothify('SN-########'),
            'manufacturer' => fake()->optional()->company(),
            'model' => fake()->optional()->bothify('Model-###'),
            'status' => fake()->randomElement(['operational', 'down', 'maintenance']),
            'specifications' => fake()->optional()->passthrough([
                'voltage' => '220V',
                'power' => fake()->numberBetween(100, 2000) . 'W',
                'weight' => fake()->numberBetween(1, 100) . 'kg',
            ]),
            'department_id' => fake()->optional()->passthrough(Department::factory()),
            'user_id' => fake()->optional()->passthrough(User::factory()),
        ];
    }

    public function disposed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'disposed',
            'disposed_date' => fake()->dateTimeBetween('-1 year', 'now'),
            'disposal_reason' => fake()->sentence(),
            'disposed_by' => User::factory(),
        ]);
    }

    public function withComponent(): static
    {
        return $this->state(fn (array $attributes) => [
            'parent_asset_id' => Asset::factory(),
            'component_type' => fake()->randomElement(['consumable', 'replaceable', 'integral']),
            'installed_date' => fake()->dateTimeBetween('-1 year', 'now'),
        ]);
    }

    public function withLifetimeTracking(): static
    {
        return $this->state(fn (array $attributes) => [
            'lifetime_unit' => fake()->randomElement(['days', 'kilometers', 'machine_hours', 'cycles']),
            'expected_lifetime_value' => fake()->numberBetween(100, 1000),
            'installed_date' => fake()->dateTimeBetween('-1 year', 'now'),
        ]);
    }
}
