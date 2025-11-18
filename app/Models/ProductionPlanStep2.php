<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class ProductionPlanStep2 extends Model
{
    use HasFactory;

    protected $table = 'production_plan_step2';

    protected $fillable = [
        'production_plan_id',
        'adonan_item_id',
        'gelondongan_item_id',
        'qty_gl1_adonan',
        'qty_gl1_gelondongan',
        'qty_gl2_adonan',
        'qty_gl2_gelondongan',
        'qty_ta_adonan',
        'qty_ta_gelondongan',
        'qty_bl_adonan',
        'qty_bl_gelondongan',
    ];

    protected $casts = [
        'qty_gl1_adonan' => 'integer',
        'qty_gl1_gelondongan' => 'integer',
        'qty_gl2_adonan' => 'integer',
        'qty_gl2_gelondongan' => 'integer',
        'qty_ta_adonan' => 'integer',
        'qty_ta_gelondongan' => 'integer',
        'qty_bl_adonan' => 'integer',
        'qty_bl_gelondongan' => 'integer',
    ];

    public function productionPlan(): BelongsTo
    {
        return $this->belongsTo(ProductionPlan::class);
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
        return (float) ($this->qty_gl1_adonan + $this->qty_gl2_adonan + $this->qty_ta_adonan + $this->qty_bl_adonan);
    }

    public function getTotalGelondonganAttribute(): float
    {
        return (float) ($this->qty_gl1_gelondongan + $this->qty_gl2_gelondongan + $this->qty_ta_gelondongan + $this->qty_bl_gelondongan);
    }
}
