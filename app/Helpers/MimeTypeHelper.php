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

    /**
     * Convert an array of file extensions to a mimetypes validation string
     *
     * @param array<string> $extensions Array of file extensions (e.g., ['pdf', 'docx', 'xlsx'])
     * @return string Mimetypes validation string (e.g., 'application/pdf,application/vnd.openxmlformats-officedocument.wordprocessingml.document')
     */
    public static function extensionsToMimetypes(array $extensions): string
    {
        $mimeTypes = [];
        foreach ($extensions as $extension) {
            $mimeType = self::getMimeType($extension);
            // Include zip for docx/xlsx files since they may be detected as zip
            if (in_array(strtolower($extension), ['docx', 'xlsx'], true)) {
                $mimeTypes[] = 'application/zip';
            }
            if ($mimeType !== 'application/octet-stream' || strtolower($extension) === 'zip') {
                $mimeTypes[] = $mimeType;
            }
        }
        
        // Remove duplicates and return comma-separated string
        return implode(',', array_unique($mimeTypes));
    }
}























