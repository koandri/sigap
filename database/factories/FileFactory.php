<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\FileCategory;
use App\Models\Asset;
use App\Models\File;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\File>
 */
final class FileFactory extends Factory
{
    protected $model = File::class;

    public function definition(): array
    {
        return [
            'fileable_type' => Asset::class,
            'fileable_id' => Asset::factory(),
            'file_category' => FileCategory::Photo,
            'file_path' => 'files/photo/' . fake()->uuid() . '.jpg',
            'file_name' => fake()->word() . '.jpg',
            'file_size' => fake()->numberBetween(100000, 5000000),
            'mime_type' => 'image/jpeg',
            'uploaded_at' => fake()->dateTimeBetween('-1 year', 'now'),
            'uploaded_by' => User::factory(),
            'metadata' => null,
            'is_primary' => false,
            'caption' => fake()->optional()->sentence(),
            'sort_order' => 0,
        ];
    }

    public function photo(): static
    {
        return $this->state(fn (array $attributes) => [
            'file_category' => FileCategory::Photo,
            'file_path' => 'files/photo/' . fake()->uuid() . '.jpg',
            'file_name' => fake()->word() . '.jpg',
            'mime_type' => 'image/jpeg',
        ]);
    }

    public function document(): static
    {
        return $this->state(fn (array $attributes) => [
            'file_category' => FileCategory::Document,
            'file_path' => 'files/document/' . fake()->uuid() . '.pdf',
            'file_name' => fake()->word() . '.pdf',
            'mime_type' => 'application/pdf',
        ]);
    }

    public function primary(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_primary' => true,
        ]);
    }

    public function withGpsData(): static
    {
        return $this->state(fn (array $attributes) => [
            'metadata' => [
                'gps_data' => [
                    'latitude' => fake()->latitude(),
                    'longitude' => fake()->longitude(),
                    'altitude' => fake()->optional()->randomFloat(2, 0, 1000),
                    'accuracy' => fake()->optional()->randomFloat(2, 0, 100),
                ],
            ],
        ]);
    }
}
