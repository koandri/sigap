<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class BomIngredient extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array<string>
     */
    protected $fillable = [
        'bom_template_id',
        'ingredient_item_id',
        'quantity',
        'unit',
        'sort_order',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'quantity' => 'decimal:3',
    ];

    /**
     * Get the BoM template that owns this ingredient.
     */
    public function bomTemplate(): BelongsTo
    {
        return $this->belongsTo(BomTemplate::class);
    }

    /**
     * Get the ingredient item.
     */
    public function ingredientItem(): BelongsTo
    {
        return $this->belongsTo(Item::class, 'ingredient_item_id');
    }

    /**
     * Scope to order by sort order.
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order');
    }

    /**
     * Get the display unit (ingredient unit or template unit).
     */
    public function getDisplayUnitAttribute(): string
    {
        return $this->unit ?: ($this->ingredientItem->unit ?? 'unit');
    }

    /**
     * Get the formatted quantity with unit.
     */
    public function getFormattedQuantityAttribute(): string
    {
        return number_format($this->quantity, 3) . ' ' . $this->display_unit;
    }
}
