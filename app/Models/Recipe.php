<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

final class Recipe extends Model
{
    use HasFactory;

    protected $fillable = [
        'dough_item_id',
        'name',
        'recipe_date',
        'description',
        'is_active',
        'created_by',
    ];

    protected $casts = [
        'recipe_date' => 'date',
        'is_active' => 'boolean',
    ];

    public function doughItem(): BelongsTo
    {
        return $this->belongsTo(Item::class, 'dough_item_id');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function ingredients(): HasMany
    {
        return $this->hasMany(RecipeIngredient::class)->orderBy('sort_order');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeForDough($query, int $doughItemId)
    {
        return $query->where('dough_item_id', $doughItemId);
    }
}
