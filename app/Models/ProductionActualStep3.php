<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class ProductionActualStep3 extends Model
{
    use HasFactory;

    protected $table = 'production_actual_step3';

    protected $fillable = [
        'production_actual_id',
        'production_plan_step3_id',
        'gelondongan_item_id',
        'kerupuk_kering_item_id',
        'actual_qty_gl1_gelondongan',
        'actual_qty_gl1_kg',
        'actual_qty_gl2_gelondongan',
        'actual_qty_gl2_kg',
        'actual_qty_ta_gelondongan',
        'actual_qty_ta_kg',
        'actual_qty_bl_gelondongan',
        'actual_qty_bl_kg',
        'recorded_at',
    ];

    protected $casts = [
        'actual_qty_gl1_gelondongan' => 'integer',
        'actual_qty_gl1_kg' => 'decimal:2',
        'actual_qty_gl2_gelondongan' => 'integer',
        'actual_qty_gl2_kg' => 'decimal:2',
        'actual_qty_ta_gelondongan' => 'integer',
        'actual_qty_ta_kg' => 'decimal:2',
        'actual_qty_bl_gelondongan' => 'integer',
        'actual_qty_bl_kg' => 'decimal:2',
        'recorded_at' => 'datetime',
    ];

    public function productionActual(): BelongsTo
    {
        return $this->belongsTo(ProductionActual::class);
    }

    public function productionPlanStep3(): BelongsTo
    {
        return $this->belongsTo(ProductionPlanStep3::class);
    }

    public function gelondonganItem(): BelongsTo
    {
        return $this->belongsTo(Item::class, 'gelondongan_item_id');
    }

    public function kerupukKeringItem(): BelongsTo
    {
        return $this->belongsTo(Item::class, 'kerupuk_kering_item_id');
    }

    public function getTotalGelondonganAttribute(): float
    {
        return (float) ($this->actual_qty_gl1_gelondongan + $this->actual_qty_gl2_gelondongan + $this->actual_qty_ta_gelondongan + $this->actual_qty_bl_gelondongan);
    }

    public function getTotalKgAttribute(): float
    {
        return (float) ($this->actual_qty_gl1_kg + $this->actual_qty_gl2_kg + $this->actual_qty_ta_kg + $this->actual_qty_bl_kg);
    }
}
