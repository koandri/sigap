<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\UsageUnit;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;

final class AssetCategory extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<string>
     */
    protected $fillable = [
        'name',
        'code',
        'description',
        'is_active',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * Get all assets for this category.
     */
    public function assets(): HasMany
    {
        return $this->hasMany(Asset::class);
    }

    /**
     * Get all usage types for this category.
     */
    public function usageTypes(): HasMany
    {
        return $this->hasMany(AssetCategoryUsageType::class);
    }

    /**
     * Scope to get only active categories.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Get average lifetime for this category.
     * Calculates on-the-fly from disposed assets.
     */
    public function getAverageLifetime(?UsageUnit $unit = null): ?float
    {
        $query = $this->assets()
            ->whereNotNull('actual_lifetime_value')
            ->where('status', 'disposed');

        if ($unit !== null) {
            $query->where('lifetime_unit', $unit->value);
        }

        return (float) $query->avg('actual_lifetime_value');
    }
}