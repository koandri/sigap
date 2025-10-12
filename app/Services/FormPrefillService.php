<?php

namespace App\Services;

use App\Models\Form;

class FormPrefillService
{
    public static function generatePrefillUrl(Form $form, array $data, bool $signed = false): string
    {
        $baseUrl = route('formsubmissions.forms.create', $form);
        
        if ($signed) {
            return self::generateSignedUrl($baseUrl, $data);
        }
        
        // Simple prefill parameters
        $params = [];
        foreach ($data as $key => $value) {
            $params["prefill[{$key}]"] = $value;
        }
        
        return $baseUrl . '?' . http_build_query($params);
    }
    
    public static function generateEncodedUrl(Form $form, array $data): string
    {
        $baseUrl = route('formsubmissions.forms.create', $form);
        $encodedData = base64_encode(json_encode($data));
        
        return $baseUrl . '?data=' . $encodedData;
    }
    
    private static function generateSignedUrl(string $baseUrl, array $data): string
    {
        // Implementation dengan Laravel's signed URLs
        return URL::temporarySignedRoute(
            'formsubmissions.forms.create.signed',
            now()->addHours(24),
            ['form' => $form->id, 'prefill' => $data]
        );
    }
}