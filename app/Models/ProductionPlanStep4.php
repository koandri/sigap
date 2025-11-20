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
        'qty_gl1_kg' => 'decimal:2',
        'qty_gl1_packing' => 'integer',
        'qty_gl2_kg' => 'decimal:2',
        'qty_gl2_packing' => 'integer',
        'qty_ta_kg' => 'decimal:2',
        'qty_ta_packing' => 'integer',
        'qty_bl_kg' => 'decimal:2',
        'qty_bl_packing' => 'integer',
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

    public function getWeightPerUnitAttribute(): float
    {
        $config = KerupukPackConfiguration::where('kerupuk_kg_item_id', $this->kerupuk_kering_item_id)
            ->where('pack_item_id', $this->kerupuk_packing_item_id)
            ->where('is_active', true)
            ->first();

        if ($config && $config->qty_kg_per_pack > 0) {
            return (float) $config->qty_kg_per_pack;
        }

        return 1.0; // Default fallback
    }

    public function materials(): HasMany
    {
        return $this->hasMany(ProductionPlanStep4Material::class);
    }

    public function getTotalKgAttribute(): float
    {
        return (float) ($this->qty_gl1_kg + $this->qty_gl2_kg + $this->qty_ta_kg + $this->qty_bl_kg);
    }

    public function actualStep4(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(ProductionActualStep4::class, 'production_plan_step4_id');
    }

    public function getTotalPackingAttribute(): float
    {
        return (float) ($this->qty_gl1_packing + $this->qty_gl2_packing + $this->qty_ta_packing + $this->qty_bl_packing);
    }
}
