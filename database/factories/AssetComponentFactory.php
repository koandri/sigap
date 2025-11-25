<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Asset;
use App\Models\AssetComponent;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\AssetComponent>
 */
final class AssetComponentFactory extends Factory
{
    protected $model = AssetComponent::class;

    public function definition(): array
    {
        return [
            'parent_asset_id' => Asset::factory(),
            'component_asset_id' => Asset::factory(),
            'component_type' => fake()->randomElement(['consumable', 'replaceable', 'integral']),
            'installed_date' => fake()->dateTimeBetween('-1 year', 'now'),
            'installed_usage_value' => fake()->optional()->randomFloat(2, 0, 1000),
            'installation_notes' => fake()->optional()->sentence(),
        ];
    }

    public function removed(): static
    {
        return $this->state(fn (array $attributes) => [
            'removed_date' => fake()->dateTimeBetween($attributes['installed_date'], 'now'),
            'removed_by' => User::factory(),
            'removal_reason' => fake()->sentence(),
            'disposed_usage_value' => fake()->randomFloat(2, 0, 1000),
        ]);
    }

    public function active(): static
    {
        return $this->state(fn (array $attributes) => [
            'removed_date' => null,
            'removed_by' => null,
            'removal_reason' => null,
        ]);
    }
}
