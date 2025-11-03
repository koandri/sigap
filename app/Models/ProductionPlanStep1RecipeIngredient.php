<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class ProductionPlanStep1RecipeIngredient extends Model
{
    use HasFactory;

    protected $table = 'production_plan_step1_recipe_ingredients';

    protected $fillable = [
        'production_plan_step1_id',
        'ingredient_item_id',
        'quantity',
        'unit',
        'sort_order',
    ];

    protected $casts = [
        'quantity' => 'decimal:3',
        'sort_order' => 'integer',
    ];

    public function productionPlanStep1(): BelongsTo
    {
        return $this->belongsTo(ProductionPlanStep1::class);
    }

    public function ingredientItem(): BelongsTo
    {
        return $this->belongsTo(Item::class, 'ingredient_item_id');
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order');
    }
}
