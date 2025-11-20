<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class ProductionActualStep2 extends Model
{
    use HasFactory;

    protected $table = 'production_actual_step2';

    protected $fillable = [
        'production_actual_id',
        'production_plan_step2_id',
        'adonan_item_id',
        'gelondongan_item_id',
        'actual_qty_gl1_adonan',
        'actual_qty_gl1_gelondongan',
        'actual_qty_gl2_adonan',
        'actual_qty_gl2_gelondongan',
        'actual_qty_ta_adonan',
        'actual_qty_ta_gelondongan',
        'actual_qty_bl_adonan',
        'actual_qty_bl_gelondongan',
        'recorded_at',
    ];

    protected $casts = [
        'actual_qty_gl1_adonan' => 'integer',
        'actual_qty_gl1_gelondongan' => 'integer',
        'actual_qty_gl2_adonan' => 'integer',
        'actual_qty_gl2_gelondongan' => 'integer',
        'actual_qty_ta_adonan' => 'integer',
        'actual_qty_ta_gelondongan' => 'integer',
        'actual_qty_bl_adonan' => 'integer',
        'actual_qty_bl_gelondongan' => 'integer',
        'recorded_at' => 'datetime',
    ];

    public function productionActual(): BelongsTo
    {
        return $this->belongsTo(ProductionActual::class);
    }

    public function productionPlanStep2(): BelongsTo
    {
        return $this->belongsTo(ProductionPlanStep2::class);
    }

    public function adonanItem(): BelongsTo
    {
        return $this->belongsTo(Item::class, 'adonan_item_id');
    }

    public function gelondonganItem(): BelongsTo
    {
        return $this->belongsTo(Item::class, 'gelondongan_item_id');
    }

    public function getTotalAdonanAttribute(): float
    {
        return (float) ($this->actual_qty_gl1_adonan + $this->actual_qty_gl2_adonan + $this->actual_qty_ta_adonan + $this->actual_qty_bl_adonan);
    }

    public function getTotalGelondonganAttribute(): float
    {
        return (float) ($this->actual_qty_gl1_gelondongan + $this->actual_qty_gl2_gelondongan + $this->actual_qty_ta_gelondongan + $this->actual_qty_bl_gelondongan);
    }
}
