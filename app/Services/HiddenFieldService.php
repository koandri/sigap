<?php

namespace App\Services;

use App\Models\FormField;
use App\Models\FormSubmission;
use Illuminate\Support\Str;

class HiddenFieldService
{
    /**
     * Generate dynamic value for hidden field
     */
    public function generateValue(FormField $field, FormSubmission $submission = null): string
    {
        if (!$field->isHidden() || !$field->validation_rules) {
            return '';
        }
        
        $rules = $field->validation_rules;
        $valueType = $rules['value_type'] ?? 'static';
        
        if ($valueType === 'static') {
            return $rules['default_value'] ?? '';
        }
        
        // Dynamic value generation
        $dynamicType = $rules['dynamic_type'] ?? '';
        $user = auth()->user();
        
        switch ($dynamicType) {
            case 'current_date':
                return now()->format('Y-m-d');
                
            case 'current_datetime':
                return now()->format('Y-m-d H:i:s');
                
            case 'user_id':
                return (string)$user->id;
                
            case 'user_name':
                return $user->name;
                
            case 'department_code':
                $primaryDept = $user->departments->first();
                return $primaryDept ? $primaryDept->code : '';
                
            case 'department_name':
                $primaryDept = $user->departments->first();
                return $primaryDept ? $primaryDept->name : '';
                
            case 'submission_code':
                return $submission ? $submission->submission_code : '';
                
            case 'random_number':
                return (string)random_int(100000, 999999);
                
            default:
                return $rules['default_value'] ?? '';
        }
    }
    
    /**
     * Process all hidden fields for a submission
     */
    public function processHiddenFields(FormSubmission $submission): void
    {
        $hiddenFields = $submission->formVersion->fields()
            ->where('field_type', 'hidden')
            ->get();
        
        foreach ($hiddenFields as $field) {
            $value = $this->generateValue($field, $submission);
            
            // Create or update answer
            $answer = $submission->answers()->where('form_field_id', $field->id)->first();
            
            if ($answer) {
                $answer->update([
                    'answer_value' => $value,
                    'answer_metadata' => [
                        'hidden_field' => true,
                        'generated_at' => now()->toISOString(),
                        'value_type' => $field->validation_rules['value_type'] ?? 'static'
                    ]
                ]);
            } else {
                $submission->answers()->create([
                    'form_field_id' => $field->id,
                    'answer_value' => $value,
                    'answer_metadata' => [
                        'hidden_field' => true,
                        'generated_at' => now()->toISOString(),
                        'value_type' => $field->validation_rules['value_type'] ?? 'static'
                    ]
                ]);
            }
        }
    }
}