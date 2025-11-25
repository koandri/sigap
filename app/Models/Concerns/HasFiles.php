<?php

declare(strict_types=1);

namespace App\Models\Concerns;

use App\Enums\FileCategory;
use App\Models\File;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Http\UploadedFile;

trait HasFiles
{
    /**
     * Get all files for this model.
     */
    public function files(): MorphMany
    {
        return $this->morphMany(File::class, 'fileable');
    }

    /**
     * Get all photos for this model.
     */
    public function photos(): MorphMany
    {
        return $this->morphMany(File::class, 'fileable')
            ->where('file_category', FileCategory::Photo)
            ->whereNotNull('file_path')
            ->orderBy('sort_order')
            ->orderBy('uploaded_at');
    }

    /**
     * Get all documents for this model.
     */
    public function documents(): MorphMany
    {
        return $this->morphMany(File::class, 'fileable')
            ->where('file_category', FileCategory::Document)
            ->orderBy('sort_order')
            ->orderBy('uploaded_at');
    }

    /**
     * Get files of a specific category.
     */
    public function filesOfCategory(FileCategory $category): MorphMany
    {
        return $this->morphMany(File::class, 'fileable')
            ->where('file_category', $category)
            ->orderBy('sort_order')
            ->orderBy('uploaded_at');
    }

    /**
     * Get the primary photo.
     */
    public function primaryPhoto(): ?File
    {
        return $this->photos()
            ->where('is_primary', true)
            ->first()
            ?? $this->photos()->first();
    }

    /**
     * Add a file to this model.
     */
    public function addFile(
        UploadedFile $uploadedFile,
        FileCategory $category,
        ?string $caption = null,
        ?array $metadata = null,
        bool $isPrimary = false
    ): File {
        // Generate storage path
        $path = $uploadedFile->store(
            'files/' . $category->value . '/' . class_basename($this),
            's3'
        );

        return $this->files()->create([
            'file_category' => $category,
            'file_path' => $path,
            'file_name' => $uploadedFile->getClientOriginalName(),
            'file_size' => $uploadedFile->getSize(),
            'mime_type' => $uploadedFile->getMimeType(),
            'uploaded_at' => now(),
            'uploaded_by' => auth()->id(),
            'metadata' => $metadata,
            'is_primary' => $isPrimary,
            'caption' => $caption,
        ]);
    }

    /**
     * Add a file from existing path (for migrations).
     */
    public function addFileFromPath(
        string $path,
        FileCategory $category,
        ?string $fileName = null,
        ?array $metadata = null,
        bool $isPrimary = false,
        ?string $caption = null,
        ?int $sortOrder = 0
    ): File {
        return $this->files()->create([
            'file_category' => $category,
            'file_path' => $path,
            'file_name' => $fileName ?? basename($path),
            'uploaded_at' => now(),
            'uploaded_by' => auth()->id(),
            'metadata' => $metadata,
            'is_primary' => $isPrimary,
            'caption' => $caption,
            'sort_order' => $sortOrder ?? 0,
        ]);
    }

    /**
     * Set a photo as primary.
     */
    public function setPrimaryPhoto(File $photo): void
    {
        if ($photo->fileable_id !== $this->id || $photo->fileable_type !== static::class) {
            throw new \InvalidArgumentException('Photo does not belong to this model');
        }

        // Remove primary from all photos
        $this->photos()->update(['is_primary' => false]);

        // Set new primary
        $photo->update(['is_primary' => true]);
    }

    /**
     * Check if model has any files.
     */
    public function hasFiles(): bool
    {
        return $this->files()->exists();
    }

    /**
     * Check if model has any photos.
     */
    public function hasPhotos(): bool
    {
        return $this->photos()->exists();
    }

    /**
     * Check if model has any documents.
     */
    public function hasDocuments(): bool
    {
        return $this->documents()->exists();
    }

    /**
     * Get total file size in bytes.
     */
    public function getTotalFileSize(): int
    {
        return $this->files()->sum('file_size') ?? 0;
    }

    /**
     * Get formatted total file size.
     */
    public function getFormattedTotalFileSize(): string
    {
        $totalSize = $this->getTotalFileSize();

        if ($totalSize === 0) {
            return '0 B';
        }

        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $size = $totalSize;
        $unit = 0;

        while ($size >= 1024 && $unit < count($units) - 1) {
            $size /= 1024;
            $unit++;
        }

        return round($size, 2) . ' ' . $units[$unit];
    }
}
