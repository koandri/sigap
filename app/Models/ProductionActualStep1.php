<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class ProductionActualStep1 extends Model
{
    use HasFactory;

    protected $table = 'production_actual_step1';

    protected $fillable = [
        'production_actual_id',
        'production_plan_step1_id',
        'dough_item_id',
        'actual_qty_gl1',
        'actual_qty_gl2',
        'actual_qty_ta',
        'actual_qty_bl',
        'recorded_at',
    ];

    protected $casts = [
        'actual_qty_gl1' => 'integer',
        'actual_qty_gl2' => 'integer',
        'actual_qty_ta' => 'integer',
        'actual_qty_bl' => 'integer',
        'recorded_at' => 'datetime',
    ];

    public function productionActual(): BelongsTo
    {
        return $this->belongsTo(ProductionActual::class);
    }

    public function productionPlanStep1(): BelongsTo
    {
        return $this->belongsTo(ProductionPlanStep1::class);
    }

    public function doughItem(): BelongsTo
    {
        return $this->belongsTo(Item::class, 'dough_item_id');
    }

    public function getTotalQuantityAttribute(): float
    {
        return (float) ($this->actual_qty_gl1 + $this->actual_qty_gl2 + $this->actual_qty_ta + $this->actual_qty_bl);
    }
}
