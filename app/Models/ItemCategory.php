<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

final class ItemCategory extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array<string>
     */
    protected $fillable = [
        'name',
        'description',
    ];

    /**
     * Get all items in this category.
     */
    public function items(): HasMany
    {
        return $this->hasMany(Item::class);
    }

    /**
     * Get active items in this category.
     */
    public function activeItems(): HasMany
    {
        return $this->hasMany(Item::class)->where('is_active', true);
    }
}
