<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\UsageUnit;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class AssetLifetimeMetric extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array<string>
     */
    protected $fillable = [
        'asset_category_id',
        'usage_type_id',
        'lifetime_unit',
        'average_lifetime',
        'median_lifetime',
        'min_lifetime',
        'max_lifetime',
        'sample_size',
        'calculated_at',
        'calculated_by',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'average_lifetime' => 'decimal:2',
        'median_lifetime' => 'decimal:2',
        'min_lifetime' => 'decimal:2',
        'max_lifetime' => 'decimal:2',
        'calculated_at' => 'datetime',
        'lifetime_unit' => UsageUnit::class,
    ];

    /**
     * Get the category for this metric.
     */
    public function assetCategory(): BelongsTo
    {
        return $this->belongsTo(AssetCategory::class);
    }

    /**
     * Get the usage type for this metric.
     */
    public function usageType(): BelongsTo
    {
        return $this->belongsTo(AssetCategoryUsageType::class, 'usage_type_id');
    }

    /**
     * Get the user who calculated this metric.
     */
    public function calculatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'calculated_by');
    }
}
