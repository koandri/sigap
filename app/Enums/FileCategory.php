<?php

declare(strict_types=1);

namespace App\Enums;

enum FileCategory: string
{
    case Photo = 'photo';
    case Document = 'document';
    case Video = 'video';
    case Audio = 'audio';
    case Report = 'report';
    case Invoice = 'invoice';
    case Certificate = 'certificate';
    case Manual = 'manual';
    case Warranty = 'warranty';
    case Other = 'other';

    /**
     * Get human-readable label for the category.
     */
    public function label(): string
    {
        return match ($this) {
            self::Photo => 'Photo',
            self::Document => 'Document',
            self::Video => 'Video',
            self::Audio => 'Audio',
            self::Report => 'Report',
            self::Invoice => 'Invoice',
            self::Certificate => 'Certificate',
            self::Manual => 'Manual',
            self::Warranty => 'Warranty',
            self::Other => 'Other',
        };
    }

    /**
     * Get icon class for the category.
     */
    public function icon(): string
    {
        return match ($this) {
            self::Photo => 'fa-image',
            self::Document => 'fa-file-alt',
            self::Video => 'fa-video',
            self::Audio => 'fa-music',
            self::Report => 'fa-file-chart',
            self::Invoice => 'fa-file-invoice',
            self::Certificate => 'fa-certificate',
            self::Manual => 'fa-book',
            self::Warranty => 'fa-shield-check',
            self::Other => 'fa-file',
        };
    }

    /**
     * Get accepted MIME types for upload.
     */
    public function acceptedMimeTypes(): array
    {
        return match ($this) {
            self::Photo => ['image/jpeg', 'image/png', 'image/gif', 'image/webp'],
            self::Video => ['video/mp4', 'video/mpeg', 'video/quicktime'],
            self::Audio => ['audio/mpeg', 'audio/wav', 'audio/ogg'],
            self::Document, self::Report, self::Invoice, self::Certificate, self::Manual, self::Warranty => [
                'application/pdf',
                'application/msword',
                'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                'application/vnd.ms-excel',
                'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            ],
            self::Other => ['*'],
        };
    }

    /**
     * Check if category is for images.
     */
    public function isImage(): bool
    {
        return $this === self::Photo;
    }

    /**
     * Check if category is for documents.
     */
    public function isDocument(): bool
    {
        return in_array($this, [
            self::Document,
            self::Report,
            self::Invoice,
            self::Certificate,
            self::Manual,
            self::Warranty,
        ]);
    }
}
