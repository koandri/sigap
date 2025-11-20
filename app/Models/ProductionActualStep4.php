<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class ProductionActualStep4 extends Model
{
    use HasFactory;

    protected $table = 'production_actual_step4';

    protected $fillable = [
        'production_actual_id',
        'production_plan_step4_id',
        'kerupuk_kering_item_id',
        'kerupuk_packing_item_id',
        'actual_qty_gl1_kg',
        'actual_qty_gl1_packing',
        'actual_qty_gl2_kg',
        'actual_qty_gl2_packing',
        'actual_qty_ta_kg',
        'actual_qty_ta_packing',
        'actual_qty_bl_kg',
        'actual_qty_bl_packing',
        'recorded_at',
    ];

    protected $casts = [
        'actual_qty_gl1_kg' => 'decimal:2',
        'actual_qty_gl1_packing' => 'integer',
        'actual_qty_gl2_kg' => 'decimal:2',
        'actual_qty_gl2_packing' => 'integer',
        'actual_qty_ta_kg' => 'decimal:2',
        'actual_qty_ta_packing' => 'integer',
        'actual_qty_bl_kg' => 'decimal:2',
        'actual_qty_bl_packing' => 'integer',
        'recorded_at' => 'datetime',
    ];

    public function productionActual(): BelongsTo
    {
        return $this->belongsTo(ProductionActual::class);
    }

    public function productionPlanStep4(): BelongsTo
    {
        return $this->belongsTo(ProductionPlanStep4::class);
    }

    public function kerupukKeringItem(): BelongsTo
    {
        return $this->belongsTo(Item::class, 'kerupuk_kering_item_id');
    }

    public function kerupukPackingItem(): BelongsTo
    {
        return $this->belongsTo(Item::class, 'kerupuk_packing_item_id');
    }

    public function getTotalKgAttribute(): float
    {
        return (float) ($this->actual_qty_gl1_kg + $this->actual_qty_gl2_kg + $this->actual_qty_ta_kg + $this->actual_qty_bl_kg);
    }

    public function getTotalPackingAttribute(): float
    {
        return (float) ($this->actual_qty_gl1_packing + $this->actual_qty_gl2_packing + $this->actual_qty_ta_packing + $this->actual_qty_bl_packing);
    }
}
