<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

final class WarehouseShelf extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array<string>
     */
    protected $fillable = [
        'warehouse_id',
        'shelf_code',
        'shelf_name',
        'description',
        'max_capacity',
        'is_active',
        'sort_order'
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'is_active' => 'boolean',
        'max_capacity' => 'integer',
        'sort_order' => 'integer'
    ];

    /**
     * Get the warehouse that owns this shelf.
     */
    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class);
    }

    /**
     * Get all positions for this shelf.
     */
    public function shelfPositions(): HasMany
    {
        return $this->hasMany(ShelfPosition::class);
    }

    /**
     * Get active positions for this shelf.
     */
    public function activePositions(): HasMany
    {
        return $this->hasMany(ShelfPosition::class)->where('is_active', true);
    }

    /**
     * Get the occupancy rate for this shelf.
     */
    public function getOccupancyRateAttribute(): float
    {
        $totalPositions = $this->shelfPositions()->count();
        $occupiedPositions = $this->shelfPositions()
            ->whereHas('positionItems', function($q) {
                $q->where('quantity', '>', 0);
            })->count();
            
        return $totalPositions > 0 ? round(($occupiedPositions / $totalPositions) * 100, 1) : 0;
    }

    /**
     * Get the number of occupied positions.
     */
    public function getOccupiedPositionsAttribute(): int
    {
        return $this->shelfPositions()
            ->whereHas('positionItems', function($q) {
                $q->where('quantity', '>', 0);
            })->count();
    }

    /**
     * Get the number of available positions.
     */
    public function getAvailablePositionsAttribute(): int
    {
        return $this->shelfPositions()
            ->whereDoesntHave('positionItems', function($q) {
                $q->where('quantity', '>', 0);
            })->count();
    }

    /**
     * Check if shelf is full.
     */
    public function getIsFullAttribute(): bool
    {
        return $this->occupied_positions >= $this->max_capacity;
    }

    /**
     * Get all items in this shelf across all positions.
     */
    public function getAllItemsAttribute()
    {
        return $this->shelfPositions()
            ->with(['positionItems.item.itemCategory'])
            ->get()
            ->pluck('positionItems')
            ->flatten()
            ->where('quantity', '>', 0);
    }

    /**
     * Scope to get only active shelves.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope to get shelves by warehouse.
     */
    public function scopeByWarehouse($query, $warehouseId)
    {
        return $query->where('warehouse_id', $warehouseId);
    }

    /**
     * Scope to get shelves by row.
     */
    public function scopeByRow($query, $row)
    {
        return $query->where('shelf_code', 'like', $row . '-%');
    }
}
