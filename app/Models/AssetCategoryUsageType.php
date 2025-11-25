<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\UsageUnit;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

final class AssetCategoryUsageType extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array<string>
     */
    protected $fillable = [
        'asset_category_id',
        'name',
        'description',
        'lifetime_unit',
        'expected_average_lifetime',
        'is_active',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'expected_average_lifetime' => 'decimal:2',
        'is_active' => 'boolean',
        'lifetime_unit' => UsageUnit::class,
    ];

    /**
     * Get the category that owns this usage type.
     */
    public function assetCategory(): BelongsTo
    {
        return $this->belongsTo(AssetCategory::class);
    }

    /**
     * Get all assets using this usage type.
     */
    public function assets(): HasMany
    {
        return $this->hasMany(Asset::class, 'usage_type_id');
    }

    /**
     * Scope to get only active usage types.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Get calculated average lifetime from metrics table.
     */
    public function getCalculatedAverageLifetime(): ?float
    {
        $metric = AssetLifetimeMetric::where('asset_category_id', $this->asset_category_id)
            ->where('usage_type_id', $this->id)
            ->where('lifetime_unit', $this->lifetime_unit->value)
            ->latest('calculated_at')
            ->first();

        return $metric?->average_lifetime;
    }
}
