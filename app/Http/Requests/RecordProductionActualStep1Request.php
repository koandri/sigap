<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class RecordProductionActualStep1Request extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('manufacturing.production-plans.record-actuals');
    }

    public function rules(): array
    {
        return [
            'step1' => 'required|array|min:1',
            'step1.*.production_plan_step1_id' => 'required|exists:production_plan_step1,id',
            'step1.*.actual_qty_gl1' => 'required|integer|min:0',
            'step1.*.actual_qty_gl2' => 'required|integer|min:0',
            'step1.*.actual_qty_ta' => 'required|integer|min:0',
            'step1.*.actual_qty_bl' => 'required|integer|min:0',
        ];
    }

    public function messages(): array
    {
        return [
            'step1.required' => 'Step 1 data is required.',
            'step1.array' => 'Step 1 data must be an array.',
            'step1.min' => 'At least one step 1 entry is required.',
            'step1.*.production_plan_step1_id.required' => 'Production plan step 1 ID is required.',
            'step1.*.production_plan_step1_id.exists' => 'Selected production plan step 1 does not exist.',
            'step1.*.actual_qty_gl1.required' => 'GL1 actual quantity is required.',
            'step1.*.actual_qty_gl1.integer' => 'GL1 actual quantity must be a whole number.',
            'step1.*.actual_qty_gl1.min' => 'GL1 actual quantity must be at least 0.',
            'step1.*.actual_qty_gl2.required' => 'GL2 actual quantity is required.',
            'step1.*.actual_qty_gl2.integer' => 'GL2 actual quantity must be a whole number.',
            'step1.*.actual_qty_gl2.min' => 'GL2 actual quantity must be at least 0.',
            'step1.*.actual_qty_ta.required' => 'TA actual quantity is required.',
            'step1.*.actual_qty_ta.integer' => 'TA actual quantity must be a whole number.',
            'step1.*.actual_qty_ta.min' => 'TA actual quantity must be at least 0.',
            'step1.*.actual_qty_bl.required' => 'BL actual quantity is required.',
            'step1.*.actual_qty_bl.integer' => 'BL actual quantity must be a whole number.',
            'step1.*.actual_qty_bl.min' => 'BL actual quantity must be at least 0.',
        ];
    }
}
