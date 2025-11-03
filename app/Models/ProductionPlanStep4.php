<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

final class ProductionPlanStep4 extends Model
{
    use HasFactory;

    protected $table = 'production_plan_step4';

    protected $fillable = [
        'production_plan_id',
        'kerupuk_kering_item_id',
        'kerupuk_packing_item_id',
        'weight_per_unit',
        'qty_gl1_kg',
        'qty_gl1_packing',
        'qty_gl2_kg',
        'qty_gl2_packing',
        'qty_ta_kg',
        'qty_ta_packing',
        'qty_bl_kg',
        'qty_bl_packing',
    ];

    protected $casts = [
        'weight_per_unit' => 'decimal:3',
        'qty_gl1_kg' => 'decimal:3',
        'qty_gl1_packing' => 'decimal:3',
        'qty_gl2_kg' => 'decimal:3',
        'qty_gl2_packing' => 'decimal:3',
        'qty_ta_kg' => 'decimal:3',
        'qty_ta_packing' => 'decimal:3',
        'qty_bl_kg' => 'decimal:3',
        'qty_bl_packing' => 'decimal:3',
    ];

    public function productionPlan(): BelongsTo
    {
        return $this->belongsTo(ProductionPlan::class);
    }

    public function kerupukKeringItem(): BelongsTo
    {
        return $this->belongsTo(Item::class, 'kerupuk_kering_item_id');
    }

    public function kerupukPackingItem(): BelongsTo
    {
        return $this->belongsTo(Item::class, 'kerupuk_packing_item_id');
    }

    public function packingMaterialRequirements(): HasMany
    {
        return $this->hasMany(PackingMaterialRequirement::class);
    }

    public function getTotalKgAttribute(): float
    {
        return (float) ($this->qty_gl1_kg + $this->qty_gl2_kg + $this->qty_ta_kg + $this->qty_bl_kg);
    }

    public function getTotalPackingAttribute(): float
    {
        return (float) ($this->qty_gl1_packing + $this->qty_gl2_packing + $this->qty_ta_packing + $this->qty_bl_packing);
    }
}
