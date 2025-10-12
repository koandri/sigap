<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

final class Item extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array<string>
     */
    protected $fillable = [
        'accurate_id',
        'shortname',
        'name',
        'item_category_id',
        'unit',
        'merk',
        'qty_kg_per_pack',
        'is_active',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'qty_kg_per_pack' => 'integer',
        'is_active' => 'boolean',
    ];

    /**
     * Get the category that owns the item.
     */
    public function itemCategory(): BelongsTo
    {
        return $this->belongsTo(ItemCategory::class);
    }

    /**
     * Get all position items for this item.
     */
    public function positionItems(): HasMany
    {
        return $this->hasMany(PositionItem::class);
    }

    /**
     * Get active position items for this item.
     */
    public function activePositionItems(): HasMany
    {
        return $this->hasMany(PositionItem::class)
            ->whereHas('shelfPosition.warehouseShelf.warehouse', function ($query) {
                $query->where('is_active', true);
            });
    }

    /**
     * Scope to get only active items.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope to filter by category.
     */
    public function scopeByCategory($query, $categoryId)
    {
        return $query->where('item_category_id', $categoryId);
    }

    /**
     * Get total quantity across all locations.
     */
    public function getTotalQuantityAttribute(): float
    {
        return (float) $this->positionItems()->sum('quantity');
    }
}
