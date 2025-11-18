<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class ProductionPlanStep4Material extends Model
{
    use HasFactory;

    protected $table = 'production_plan_step4_materials';

    protected $fillable = [
        'production_plan_step4_id',
        'packing_material_item_id',
        'quantity_total',
    ];

    protected $casts = [
        'quantity_total' => 'integer',
    ];

    public function productionPlanStep4(): BelongsTo
    {
        return $this->belongsTo(ProductionPlanStep4::class);
    }

    public function packingMaterialItem(): BelongsTo
    {
        return $this->belongsTo(Item::class, 'packing_material_item_id');
    }
}
