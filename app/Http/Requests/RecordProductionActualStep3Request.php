<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class RecordProductionActualStep3Request extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('manufacturing.production-plans.record-actuals');
    }

    public function rules(): array
    {
        return [
            'step3' => 'required|array|min:1',
            'step3.*.production_plan_step3_id' => 'required|exists:production_plan_step3,id',
            'step3.*.actual_qty_gl1_gelondongan' => 'required|integer|min:0',
            'step3.*.actual_qty_gl1_kg' => 'required|numeric|min:0',
            'step3.*.actual_qty_gl2_gelondongan' => 'required|integer|min:0',
            'step3.*.actual_qty_gl2_kg' => 'required|numeric|min:0',
            'step3.*.actual_qty_ta_gelondongan' => 'required|integer|min:0',
            'step3.*.actual_qty_ta_kg' => 'required|numeric|min:0',
            'step3.*.actual_qty_bl_gelondongan' => 'required|integer|min:0',
            'step3.*.actual_qty_bl_kg' => 'required|numeric|min:0',
        ];
    }

    public function messages(): array
    {
        return [
            'step3.required' => 'Step 3 data is required.',
            'step3.array' => 'Step 3 data must be an array.',
            'step3.min' => 'At least one step 3 entry is required.',
            'step3.*.production_plan_step3_id.required' => 'Production plan step 3 ID is required.',
            'step3.*.production_plan_step3_id.exists' => 'Selected production plan step 3 does not exist.',
            'step3.*.actual_qty_gl1_gelondongan.required' => 'GL1 Gelondongan actual quantity is required.',
            'step3.*.actual_qty_gl1_gelondongan.integer' => 'GL1 Gelondongan actual quantity must be a whole number.',
            'step3.*.actual_qty_gl1_gelondongan.min' => 'GL1 Gelondongan actual quantity must be at least 0.',
            'step3.*.actual_qty_gl1_kg.required' => 'GL1 Kg actual quantity is required.',
            'step3.*.actual_qty_gl1_kg.numeric' => 'GL1 Kg actual quantity must be a number.',
            'step3.*.actual_qty_gl1_kg.min' => 'GL1 Kg actual quantity must be at least 0.',
            'step3.*.actual_qty_gl2_gelondongan.required' => 'GL2 Gelondongan actual quantity is required.',
            'step3.*.actual_qty_gl2_gelondongan.integer' => 'GL2 Gelondongan actual quantity must be a whole number.',
            'step3.*.actual_qty_gl2_gelondongan.min' => 'GL2 Gelondongan actual quantity must be at least 0.',
            'step3.*.actual_qty_gl2_kg.required' => 'GL2 Kg actual quantity is required.',
            'step3.*.actual_qty_gl2_kg.numeric' => 'GL2 Kg actual quantity must be a number.',
            'step3.*.actual_qty_gl2_kg.min' => 'GL2 Kg actual quantity must be at least 0.',
            'step3.*.actual_qty_ta_gelondongan.required' => 'TA Gelondongan actual quantity is required.',
            'step3.*.actual_qty_ta_gelondongan.integer' => 'TA Gelondongan actual quantity must be a whole number.',
            'step3.*.actual_qty_ta_gelondongan.min' => 'TA Gelondongan actual quantity must be at least 0.',
            'step3.*.actual_qty_ta_kg.required' => 'TA Kg actual quantity is required.',
            'step3.*.actual_qty_ta_kg.numeric' => 'TA Kg actual quantity must be a number.',
            'step3.*.actual_qty_ta_kg.min' => 'TA Kg actual quantity must be at least 0.',
            'step3.*.actual_qty_bl_gelondongan.required' => 'BL Gelondongan actual quantity is required.',
            'step3.*.actual_qty_bl_gelondongan.integer' => 'BL Gelondongan actual quantity must be a whole number.',
            'step3.*.actual_qty_bl_gelondongan.min' => 'BL Gelondongan actual quantity must be at least 0.',
            'step3.*.actual_qty_bl_kg.required' => 'BL Kg actual quantity is required.',
            'step3.*.actual_qty_bl_kg.numeric' => 'BL Kg actual quantity must be a number.',
            'step3.*.actual_qty_bl_kg.min' => 'BL Kg actual quantity must be at least 0.',
        ];
    }
}
