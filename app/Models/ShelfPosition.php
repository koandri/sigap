<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

final class ShelfPosition extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array<string>
     */
    protected $fillable = [
        'warehouse_shelf_id',
        'position_code',
        'position_name',
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
     * Get the warehouse shelf that owns this position.
     */
    public function warehouseShelf(): BelongsTo
    {
        return $this->belongsTo(WarehouseShelf::class);
    }

    /**
     * Get all items in this position.
     */
    public function positionItems(): HasMany
    {
        return $this->hasMany(PositionItem::class);
    }

    /**
     * Get the current item in this position (if any).
     */
    public function getCurrentItemAttribute()
    {
        // Use loaded relationship if available to avoid N+1 queries
        if ($this->relationLoaded('positionItems')) {
            return $this->positionItems->where('quantity', '>', 0)->first();
        }
        
        return $this->positionItems()->where('quantity', '>', 0)->first();
    }

    /**
     * Check if position is occupied.
     */
    public function getIsOccupiedAttribute(): bool
    {
        // Use loaded relationship if available to avoid N+1 queries
        if ($this->relationLoaded('positionItems')) {
            return $this->positionItems->where('quantity', '>', 0)->isNotEmpty();
        }
        
        return $this->positionItems()->where('quantity', '>', 0)->exists();
    }

    /**
     * Get the full location code (e.g., A-01-01).
     */
    public function getFullLocationCodeAttribute(): string
    {
        // Accessor will use loaded relationship if available, otherwise lazy load
        $shelfCode = $this->warehouseShelf?->shelf_code ?? 'Unknown';
        return $shelfCode . '-' . $this->position_code;
    }

    /**
     * Get the full location name (e.g., "Section A-01, Position 01").
     */
    public function getFullLocationNameAttribute(): string
    {
        // Accessor will use loaded relationship if available, otherwise lazy load
        $shelfName = $this->warehouseShelf?->shelf_name ?? 'Unknown Shelf';
        return $shelfName . ', ' . $this->position_name;
    }

    /**
     * Get the total quantity of items in this position.
     */
    public function getTotalQuantityAttribute(): float
    {
        return (float) $this->positionItems()->sum('quantity');
    }

    /**
     * Scope to get only active positions.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope to get positions by shelf.
     */
    public function scopeByShelf($query, $shelfId)
    {
        return $query->where('warehouse_shelf_id', $shelfId);
    }

    /**
     * Scope to get occupied positions.
     */
    public function scopeOccupied($query)
    {
        return $query->whereHas('positionItems', function($q) {
            $q->where('quantity', '>', 0);
        });
    }

    /**
     * Scope to get empty positions.
     */
    public function scopeEmpty($query)
    {
        return $query->whereDoesntHave('positionItems', function($q) {
            $q->where('quantity', '>', 0);
        });
    }
}
