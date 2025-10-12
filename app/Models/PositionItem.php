<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class PositionItem extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array<string>
     */
    protected $fillable = [
        'shelf_position_id',
        'item_id',
        'quantity',
        'expiry_date',
        'last_updated_by',
        'last_updated_at'
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'quantity' => 'decimal:2',
        'expiry_date' => 'date',
        'last_updated_at' => 'datetime'
    ];

    /**
     * Get the shelf position that owns this item.
     */
    public function shelfPosition(): BelongsTo
    {
        return $this->belongsTo(ShelfPosition::class);
    }

    /**
     * Get the item in this position.
     */
    public function item(): BelongsTo
    {
        return $this->belongsTo(Item::class);
    }

    /**
     * Get the user who last updated this item.
     */
    public function lastUpdatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'last_updated_by');
    }

    /**
     * Get the user who last updated this item (alias for updatedBy).
     */
    public function updatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'last_updated_by');
    }

    /**
     * Get the full location path (e.g., "Finished Goods Warehouse > A-01 > Position 01").
     */
    public function getFullLocationPathAttribute(): string
    {
        $shelf = $this->shelfPosition->warehouseShelf;
        $warehouse = $shelf->warehouse;
        
        return "{$warehouse->name} > {$shelf->shelf_name} > {$this->shelfPosition->position_name}";
    }

    /**
     * Get the full location code (e.g., "A-01-01").
     */
    public function getFullLocationCodeAttribute(): string
    {
        return $this->shelfPosition->full_location_code;
    }

    /**
     * Check if item is expiring soon (within 30 days).
     */
    public function getIsExpiringSoonAttribute(): bool
    {
        if (!$this->expiry_date) {
            return false;
        }
        
        return $this->expiry_date->lte(now()->addDays(30));
    }

    /**
     * Check if item has expired.
     */
    public function getIsExpiredAttribute(): bool
    {
        if (!$this->expiry_date) {
            return false;
        }
        
        return $this->expiry_date->lt(now());
    }

    /**
     * Get days until expiry.
     */
    public function getDaysUntilExpiryAttribute(): ?int
    {
        if (!$this->expiry_date) {
            return null;
        }
        
        return now()->diffInDays($this->expiry_date, false);
    }

    /**
     * Scope to get items with stock.
     */
    public function scopeWithStock($query)
    {
        return $query->where('quantity', '>', 0);
    }

    /**
     * Scope to get expiring items.
     */
    public function scopeExpiring($query, int $days = 30)
    {
        return $query->whereNotNull('expiry_date')
            ->whereDate('expiry_date', '<=', now()->addDays($days));
    }

    /**
     * Scope to get expired items.
     */
    public function scopeExpired($query)
    {
        return $query->whereNotNull('expiry_date')
            ->whereDate('expiry_date', '<', now());
    }

    /**
     * Scope to get items by position.
     */
    public function scopeByPosition($query, $positionId)
    {
        return $query->where('shelf_position_id', $positionId);
    }

    /**
     * Scope to get items by item.
     */
    public function scopeByItem($query, $itemId)
    {
        return $query->where('item_id', $itemId);
    }
}
