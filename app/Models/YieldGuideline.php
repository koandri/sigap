<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class YieldGuideline extends Model
{
    use HasFactory;

    protected $fillable = [
        'from_item_id',
        'to_item_id',
        'from_stage',
        'to_stage',
        'yield_quantity',
        'unit',
        'is_active',
    ];

    protected $casts = [
        'yield_quantity' => 'decimal:3',
        'is_active' => 'boolean',
    ];

    public function fromItem(): BelongsTo
    {
        return $this->belongsTo(Item::class, 'from_item_id');
    }

    public function toItem(): BelongsTo
    {
        return $this->belongsTo(Item::class, 'to_item_id');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeForConversion($query, int $fromItemId, int $toItemId)
    {
        return $query->where('from_item_id', $fromItemId)
            ->where('to_item_id', $toItemId)
            ->where('is_active', true);
    }
}
