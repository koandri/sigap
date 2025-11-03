<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class ProductionPlanStep3 extends Model
{
    use HasFactory;

    protected $table = 'production_plan_step3';

    protected $fillable = [
        'production_plan_id',
        'gelondongan_item_id',
        'kerupuk_kering_item_id',
        'qty_gl1_gelondongan',
        'qty_gl1_kg',
        'qty_gl2_gelondongan',
        'qty_gl2_kg',
        'qty_ta_gelondongan',
        'qty_ta_kg',
        'qty_bl_gelondongan',
        'qty_bl_kg',
    ];

    protected $casts = [
        'qty_gl1_gelondongan' => 'decimal:3',
        'qty_gl1_kg' => 'decimal:3',
        'qty_gl2_gelondongan' => 'decimal:3',
        'qty_gl2_kg' => 'decimal:3',
        'qty_ta_gelondongan' => 'decimal:3',
        'qty_ta_kg' => 'decimal:3',
        'qty_bl_gelondongan' => 'decimal:3',
        'qty_bl_kg' => 'decimal:3',
    ];

    public function productionPlan(): BelongsTo
    {
        return $this->belongsTo(ProductionPlan::class);
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
        return (float) ($this->qty_gl1_gelondongan + $this->qty_gl2_gelondongan + $this->qty_ta_gelondongan + $this->qty_bl_gelondongan);
    }

    public function getTotalKgAttribute(): float
    {
        return (float) ($this->qty_gl1_kg + $this->qty_gl2_kg + $this->qty_ta_kg + $this->qty_bl_kg);
    }
}
