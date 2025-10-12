<?php

namespace App\Services;

use App\Models\FormSubmission;
use App\Models\FormField;

class CalculationService
{
    /**
     * Calculate field value based on formula
     */
    public function calculateFieldValue(FormField $field, $submission): ?float
    {
        if (!$field->isCalculated() || !$field->calculation_formula) {
            return null;
        }
        
        try {
            $formula = $field->calculation_formula;
            $dependencies = $field->calculation_dependencies ?? [];
            
            // Get values for dependent fields
            $values = [];
            foreach ($dependencies as $fieldCode) {
                $value = $submission->getAnswer($fieldCode);
            
                // For hidden fields, generate value if not exists
                if ($value === null) {
                    $depField = $submission->formVersion->fields()
                        ->where('field_code', $fieldCode)
                        ->first();
                    
                    if ($depField && $depField->field_type === 'hidden') {
                        $hiddenService = app(\App\Services\HiddenFieldService::class);
                        $value = $hiddenService->generateValue($depField, $submission);
                        
                        // Save the generated hidden value
                        $submission->answers()->updateOrCreate(
                            ['form_field_id' => $depField->id],
                            [
                                'answer_value' => $value,
                                'answer_metadata' => [
                                    'hidden_field' => true,
                                    'generated_at' => now()->toISOString(),
                                    'generated_for_calculation' => $field->field_code
                                ]
                            ]
                        );
                    }
                }
                
                $values[$fieldCode] = is_numeric($value) ? (float)$value : 0;
            }
            
            // Replace field codes with values in formula
            $evaluableFormula = $formula;
            foreach ($values as $fieldCode => $value) {
                $evaluableFormula = str_replace('{' . $fieldCode . '}', $value, $evaluableFormula);
            }
            
            // Evaluate the formula safely
            $result = $this->safeEvaluate($evaluableFormula);
            
            return $result;
            
        } catch (\Exception $e) {
            \Log::error('Calculation failed for field ' . $field->field_code . ': ' . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Safe mathematical expression evaluation
     */
    private function safeEvaluate($expression): ?float
    {
        // Remove any non-mathematical characters for security
        $cleanExpression = preg_replace('/[^0-9+\-*\/\.\(\)\s]/', '', $expression);
        
        // Additional safety: only allow basic math operations
        if (!preg_match('/^[0-9+\-*\/\.\(\)\s]+$/', $cleanExpression)) {
            throw new \Exception('Invalid characters in calculation');
        }
        
        // Evaluate using PHP's eval (with safety checks)
        try {
            // Create a safe evaluation context
            $result = null;
            eval('$result = ' . $cleanExpression . ';');
            
            if (!is_numeric($result)) {
                throw new \Exception('Calculation result is not numeric');
            }
            
            return (float)$result;
            
        } catch (\ParseError $e) {
            throw new \Exception('Formula syntax error: ' . $e->getMessage());
        } catch (\Error $e) {
            throw new \Exception('Calculation error: ' . $e->getMessage());
        }
    }
    
    /**
     * Format calculated value for display
     */
    public function formatValue(?float $value, string $format = 'number'): string
    {
        if ($value === null) {
            return '-';
        }
        
        switch ($format) {
            case 'currency':
                return 'Rp ' . number_format($value, 2, ',', '.');
            case 'percentage':
                return number_format($value, 2) . '%';
            case 'decimal_2':
                return number_format($value, 2, '.', ',');
            case 'decimal_0':
                return number_format($value, 0, '.', ',');
            case 'number':
            default:
                return number_format($value, 2);
        }
    }
    
    /**
     * Recalculate all calculated fields for a submission
     */
    public function recalculateSubmission(FormSubmission $submission): void
    {
        $calculatedFields = $submission->formVersion->fields()
            ->where('field_type', 'calculated')
            ->get();
        
        foreach ($calculatedFields as $field) {
            $value = $this->calculateFieldValue($field, $submission);
            
            if ($value !== null) {
                // Update or create answer
                $answer = $submission->answers()->where('form_field_id', $field->id)->first();
                
                if ($answer) {
                    $answer->update(['answer_value' => $value]);
                } else {
                    $submission->answers()->create([
                        'form_field_id' => $field->id,
                        'answer_value' => $value
                    ]);
                }
            }
        }
    }
}