<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class RecordProductionActualStep2Request extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('manufacturing.production-plans.record-actuals');
    }

    public function rules(): array
    {
        return [
            'step2' => 'required|array|min:1',
            'step2.*.production_plan_step2_id' => 'required|exists:production_plan_step2,id',
            'step2.*.actual_qty_gl1_adonan' => 'required|integer|min:0',
            'step2.*.actual_qty_gl1_gelondongan' => 'required|integer|min:0',
            'step2.*.actual_qty_gl2_adonan' => 'required|integer|min:0',
            'step2.*.actual_qty_gl2_gelondongan' => 'required|integer|min:0',
            'step2.*.actual_qty_ta_adonan' => 'required|integer|min:0',
            'step2.*.actual_qty_ta_gelondongan' => 'required|integer|min:0',
            'step2.*.actual_qty_bl_adonan' => 'required|integer|min:0',
            'step2.*.actual_qty_bl_gelondongan' => 'required|integer|min:0',
        ];
    }

    public function messages(): array
    {
        return [
            'step2.required' => 'Step 2 data is required.',
            'step2.array' => 'Step 2 data must be an array.',
            'step2.min' => 'At least one step 2 entry is required.',
            'step2.*.production_plan_step2_id.required' => 'Production plan step 2 ID is required.',
            'step2.*.production_plan_step2_id.exists' => 'Selected production plan step 2 does not exist.',
            'step2.*.actual_qty_gl1_adonan.required' => 'GL1 Adonan actual quantity is required.',
            'step2.*.actual_qty_gl1_adonan.integer' => 'GL1 Adonan actual quantity must be a whole number.',
            'step2.*.actual_qty_gl1_adonan.min' => 'GL1 Adonan actual quantity must be at least 0.',
            'step2.*.actual_qty_gl1_gelondongan.required' => 'GL1 Gelondongan actual quantity is required.',
            'step2.*.actual_qty_gl1_gelondongan.integer' => 'GL1 Gelondongan actual quantity must be a whole number.',
            'step2.*.actual_qty_gl1_gelondongan.min' => 'GL1 Gelondongan actual quantity must be at least 0.',
            'step2.*.actual_qty_gl2_adonan.required' => 'GL2 Adonan actual quantity is required.',
            'step2.*.actual_qty_gl2_adonan.integer' => 'GL2 Adonan actual quantity must be a whole number.',
            'step2.*.actual_qty_gl2_adonan.min' => 'GL2 Adonan actual quantity must be at least 0.',
            'step2.*.actual_qty_gl2_gelondongan.required' => 'GL2 Gelondongan actual quantity is required.',
            'step2.*.actual_qty_gl2_gelondongan.integer' => 'GL2 Gelondongan actual quantity must be a whole number.',
            'step2.*.actual_qty_gl2_gelondongan.min' => 'GL2 Gelondongan actual quantity must be at least 0.',
            'step2.*.actual_qty_ta_adonan.required' => 'TA Adonan actual quantity is required.',
            'step2.*.actual_qty_ta_adonan.integer' => 'TA Adonan actual quantity must be a whole number.',
            'step2.*.actual_qty_ta_adonan.min' => 'TA Adonan actual quantity must be at least 0.',
            'step2.*.actual_qty_ta_gelondongan.required' => 'TA Gelondongan actual quantity is required.',
            'step2.*.actual_qty_ta_gelondongan.integer' => 'TA Gelondongan actual quantity must be a whole number.',
            'step2.*.actual_qty_ta_gelondongan.min' => 'TA Gelondongan actual quantity must be at least 0.',
            'step2.*.actual_qty_bl_adonan.required' => 'BL Adonan actual quantity is required.',
            'step2.*.actual_qty_bl_adonan.integer' => 'BL Adonan actual quantity must be a whole number.',
            'step2.*.actual_qty_bl_adonan.min' => 'BL Adonan actual quantity must be at least 0.',
            'step2.*.actual_qty_bl_gelondongan.required' => 'BL Gelondongan actual quantity is required.',
            'step2.*.actual_qty_bl_gelondongan.integer' => 'BL Gelondongan actual quantity must be a whole number.',
            'step2.*.actual_qty_bl_gelondongan.min' => 'BL Gelondongan actual quantity must be at least 0.',
        ];
    }
}
