<?php

declare(strict_types=1);

namespace App\Helpers;

final class MimeTypeHelper
{
    public static function getMimeType(string $fileType): string
    {
        return match (strtolower($fileType)) {
            'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'xlsx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'pdf' => 'application/pdf',
            'jpg', 'jpeg' => 'image/jpeg',
            'png' => 'image/png',
            default => 'application/octet-stream',
        };
    }
}



