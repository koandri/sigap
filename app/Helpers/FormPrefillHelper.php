<?php

namespace App\Helpers;

use App\Models\Form;

class FormPrefillHelper
{
    public static function generatePrefillUrl(Form $form, array $data = []): string
    {
        $baseUrl = route('formsubmissions.create', $form);
        
        if (empty($data)) {
            return $baseUrl;
        }
        
        // Build query string
        $queryParams = [];
        foreach ($data as $fieldCode => $value) {
            if (is_array($value)) {
                // For multiple values, use comma-separated
                $queryParams[$fieldCode] = implode(',', $value);
            } else {
                $queryParams[$fieldCode] = $value;
            }
        }
        
        return $baseUrl . '?' . http_build_query($queryParams);
    }
    
    public static function generateQRCode(Form $form, array $data = []): string
    {
        $url = self::generatePrefillUrl($form, $data);
        
        return $url; // For now, return URL
    }
}