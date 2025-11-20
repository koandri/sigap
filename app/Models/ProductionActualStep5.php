<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class ProductionActualStep5 extends Model
{
    use HasFactory;

    protected $table = 'production_actual_step5';

    protected $fillable = [
        'production_actual_id',
        'production_plan_step5_id',
        'pack_sku_id',
        'packing_material_item_id',
        'actual_quantity_total',
        'recorded_at',
    ];

    protected $casts = [
        'actual_quantity_total' => 'integer',
        'recorded_at' => 'datetime',
    ];

    public function productionActual(): BelongsTo
    {
        return $this->belongsTo(ProductionActual::class);
    }

    public function productionPlanStep5(): BelongsTo
    {
        return $this->belongsTo(ProductionPlanStep5::class);
    }

    public function packSku(): BelongsTo
    {
        return $this->belongsTo(Item::class, 'pack_sku_id');
    }

    public function packingMaterialItem(): BelongsTo
    {
        return $this->belongsTo(Item::class, 'packing_material_item_id');
    }
}
