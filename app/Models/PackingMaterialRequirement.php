<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class PackingMaterialRequirement extends Model
{
    use HasFactory;

    protected $fillable = [
        'production_plan_step4_id',
        'packing_material_item_id',
        'quantity_per_unit',
    ];

    protected $casts = [
        'quantity_per_unit' => 'decimal:3',
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
