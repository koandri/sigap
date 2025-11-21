<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

final class AssetPhoto extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array<string>
     */
    protected $fillable = [
        'asset_id',
        'photo_path',
        'uploaded_at',
        'captured_at',
        'is_primary',
        'uploaded_by',
        'gps_data',
        'metadata',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'uploaded_at' => 'datetime',
        'captured_at' => 'datetime',
        'is_primary' => 'boolean',
        'gps_data' => 'array',
        'metadata' => 'array',
    ];

    /**
     * Get the asset that owns the photo.
     */
    public function asset(): BelongsTo
    {
        return $this->belongsTo(Asset::class);
    }

    /**
     * Get the user who uploaded this photo.
     */
    public function uploadedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    /**
     * Get the full URL for the photo.
     */
    public function getUrlAttribute(): string
    {
        return Storage::disk('s3')->url($this->photo_path);
    }

    /**
     * Scope to get only primary photos.
     */
    public function scopePrimary($query)
    {
        return $query->where('is_primary', true);
    }
}


