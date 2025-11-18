<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class PackingMaterialBlueprint extends Model
{
    use HasFactory;

    protected $fillable = [
        'pack_item_id',
        'material_item_id',
        'quantity_per_pack',
    ];

    protected $casts = [
        'quantity_per_pack' => 'integer',
    ];

    public function packItem(): BelongsTo
    {
        return $this->belongsTo(Item::class, 'pack_item_id');
    }

    public function materialItem(): BelongsTo
    {
        return $this->belongsTo(Item::class, 'material_item_id');
    }
}

