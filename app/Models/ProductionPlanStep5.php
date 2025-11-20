<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class ProductionPlanStep5 extends Model
{
    use HasFactory;

    protected $table = 'production_plan_step5';

    protected $fillable = [
        'production_plan_id',
        'pack_sku_id',
        'packing_material_item_id',
        'quantity_total',
    ];

    protected $casts = [
        'quantity_total' => 'integer',
    ];

    public function productionPlan(): BelongsTo
    {
        return $this->belongsTo(ProductionPlan::class);
    }

    public function packSku(): BelongsTo
    {
        return $this->belongsTo(Item::class, 'pack_sku_id');
    }

    public function packingMaterialItem(): BelongsTo
    {
        return $this->belongsTo(Item::class, 'packing_material_item_id');
    }

    public function actualStep5(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(ProductionActualStep5::class, 'production_plan_step5_id');
    }
}
