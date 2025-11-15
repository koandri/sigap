<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

final class ProductionPlanStep1 extends Model
{
    use HasFactory;

    protected $table = 'production_plan_step1';

    protected $fillable = [
        'production_plan_id',
        'dough_item_id',
        'recipe_id',
        'recipe_name',
        'recipe_date',
        'qty_gl1',
        'qty_gl2',
        'qty_ta',
        'qty_bl',
        'is_custom_recipe',
    ];

    protected $casts = [
        'recipe_date' => 'date',
        'qty_gl1' => 'integer',
        'qty_gl2' => 'integer',
        'qty_ta' => 'integer',
        'qty_bl' => 'integer',
        'is_custom_recipe' => 'boolean',
    ];

    public function productionPlan(): BelongsTo
    {
        return $this->belongsTo(ProductionPlan::class);
    }

    public function doughItem(): BelongsTo
    {
        return $this->belongsTo(Item::class, 'dough_item_id');
    }

    public function recipe(): BelongsTo
    {
        return $this->belongsTo(Recipe::class);
    }

    public function recipeIngredients(): HasMany
    {
        return $this->hasMany(ProductionPlanStep1RecipeIngredient::class)->orderBy('sort_order');
    }

    public function getTotalQuantityAttribute(): float
    {
        return (float) ($this->qty_gl1 + $this->qty_gl2 + $this->qty_ta + $this->qty_bl);
    }
}
