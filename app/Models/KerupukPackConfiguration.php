<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class KerupukPackConfiguration extends Model
{
    use HasFactory;

    protected $fillable = [
        'kerupuk_kg_item_id',
        'pack_item_id',
        'qty_kg_per_pack',
        'is_active',
    ];

    protected $casts = [
        'qty_kg_per_pack' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    public function kerupukKgItem(): BelongsTo
    {
        return $this->belongsTo(Item::class, 'kerupuk_kg_item_id');
    }

    public function packItem(): BelongsTo
    {
        return $this->belongsTo(Item::class, 'pack_item_id');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
