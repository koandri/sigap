<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\FileCategory;
use App\ValueObjects\GpsData;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Facades\Storage;

final class File extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<string>
     */
    protected $fillable = [
        'fileable_type',
        'fileable_id',
        'file_category',
        'file_path',
        'file_name',
        'file_size',
        'mime_type',
        'uploaded_at',
        'uploaded_by',
        'metadata',
        'is_primary',
        'caption',
        'sort_order',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'uploaded_at' => 'datetime',
        'metadata' => 'array',
        'is_primary' => 'boolean',
        'file_category' => FileCategory::class,
        'file_size' => 'integer',
        'sort_order' => 'integer',
    ];

    /**
     * Get the parent fileable model.
     */
    public function fileable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Get the user who uploaded this file.
     */
    public function uploadedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    /**
     * Scope to get only photos.
     */
    public function scopePhotos($query)
    {
        return $query->where('file_category', FileCategory::Photo);
    }

    /**
     * Scope to get only documents.
     */
    public function scopeDocuments($query)
    {
        return $query->where('file_category', FileCategory::Document);
    }

    /**
     * Scope to get only primary files.
     */
    public function scopePrimary($query)
    {
        return $query->where('is_primary', true);
    }

    /**
     * Scope to filter by category.
     */
    public function scopeOfCategory($query, FileCategory $category)
    {
        return $query->where('file_category', $category);
    }

    /**
     * Scope to filter files with valid paths.
     */
    public function scopeWithPath($query)
    {
        return $query->whereNotNull('file_path');
    }

    /**
     * Get the full URL for the file.
     */
    public function getUrl(): ?string
    {
        if (!$this->file_path) {
            return null;
        }
        return Storage::disk('s3')->url($this->file_path);
    }

    /**
     * Get GPS data from metadata.
     */
    public function getGpsData(): ?GpsData
    {
        if (!isset($this->metadata['gps_data'])) {
            return null;
        }

        return GpsData::fromArray($this->metadata['gps_data']);
    }

    /**
     * Set GPS data in metadata.
     */
    public function setGpsData(?GpsData $gpsData): void
    {
        $metadata = $this->metadata ?? [];
        $metadata['gps_data'] = $gpsData?->toArray();
        $this->metadata = $metadata;
        $this->save();
    }

    /**
     * Check if file is an image.
     */
    public function isImage(): bool
    {
        return str_starts_with($this->mime_type ?? '', 'image/');
    }

    /**
     * Check if file is a video.
     */
    public function isVideo(): bool
    {
        return str_starts_with($this->mime_type ?? '', 'video/');
    }

    /**
     * Check if file is a PDF.
     */
    public function isPdf(): bool
    {
        return $this->mime_type === 'application/pdf';
    }

    /**
     * Get formatted file size.
     */
    public function getFormattedSize(): string
    {
        if (!$this->file_size) {
            return 'Unknown';
        }

        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $size = $this->file_size;
        $unit = 0;

        while ($size >= 1024 && $unit < count($units) - 1) {
            $size /= 1024;
            $unit++;
        }

        return round($size, 2) . ' ' . $units[$unit];
    }

    /**
     * Get file extension.
     */
    public function getExtension(): string
    {
        return pathinfo($this->file_name, PATHINFO_EXTENSION);
    }

    /**
     * Delete file from storage.
     */
    public function deleteFromStorage(): bool
    {
        if (!$this->file_path) {
            return false;
        }
        return Storage::disk('s3')->delete($this->file_path);
    }

    /**
     * Get photo_path attribute (backward compatibility with AssetPhoto).
     */
    public function getPhotoPathAttribute(): ?string
    {
        return $this->file_path;
    }

    /**
     * Get captured_at attribute from metadata.
     */
    public function getCapturedAtAttribute(): ?\Carbon\Carbon
    {
        if (isset($this->metadata['captured_at'])) {
            try {
                return \Carbon\Carbon::parse($this->metadata['captured_at']);
            } catch (\Exception $e) {
                return null;
            }
        }
        return null;
    }

    /**
     * Boot the model.
     */
    protected static function boot()
    {
        parent::boot();

        // Delete file from storage when model is deleted
        static::deleting(function ($file) {
            $file->deleteFromStorage();
        });
    }
}
