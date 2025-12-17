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
            'xls' => 'application/vnd.ms-excel',
            'pdf' => 'application/pdf',
            'jpg', 'jpeg' => 'image/jpeg',
            'png' => 'image/png',
            'gif' => 'image/gif',
            'zip' => 'application/zip',
            default => 'application/octet-stream',
        };
    }
}























