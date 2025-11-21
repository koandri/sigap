<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class RecordProductionActualStep4Request extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('manufacturing.production-plans.record-actuals');
    }

    public function rules(): array
    {
        return [
            'step4' => 'required|array|min:1',
            'step4.*.production_plan_step4_id' => 'nullable|exists:production_plan_step4,id',
            'step4.*.kerupuk_kering_item_id' => 'required_without:step4.*.production_plan_step4_id|exists:items,id',
            'step4.*.kerupuk_packing_item_id' => 'required_without:step4.*.production_plan_step4_id|exists:items,id',
            'step4.*.actual_qty_gl1_kg' => 'required|numeric|min:0',
            'step4.*.actual_qty_gl1_packing' => 'required|integer|min:0',
            'step4.*.actual_qty_gl2_kg' => 'required|numeric|min:0',
            'step4.*.actual_qty_gl2_packing' => 'required|integer|min:0',
            'step4.*.actual_qty_ta_kg' => 'required|numeric|min:0',
            'step4.*.actual_qty_ta_packing' => 'required|integer|min:0',
            'step4.*.actual_qty_bl_kg' => 'required|numeric|min:0',
            'step4.*.actual_qty_bl_packing' => 'required|integer|min:0',
        ];
    }

    public function messages(): array
    {
        return [
            'step4.required' => 'Step 4 data is required.',
            'step4.array' => 'Step 4 data must be an array.',
            'step4.min' => 'At least one step 4 entry is required.',
            'step4.*.production_plan_step4_id.exists' => 'Selected production plan step 4 does not exist.',
            'step4.*.kerupuk_kering_item_id.required_without' => 'Kerupuk kering item is required when creating a new item.',
            'step4.*.kerupuk_kering_item_id.exists' => 'Selected kerupuk kering item does not exist.',
            'step4.*.kerupuk_packing_item_id.required_without' => 'Kerupuk packing item is required when creating a new item.',
            'step4.*.kerupuk_packing_item_id.exists' => 'Selected kerupuk packing item does not exist.',
            'step4.*.actual_qty_gl1_kg.required' => 'GL1 Kg actual quantity is required.',
            'step4.*.actual_qty_gl1_kg.numeric' => 'GL1 Kg actual quantity must be a number.',
            'step4.*.actual_qty_gl1_kg.min' => 'GL1 Kg actual quantity must be at least 0.',
            'step4.*.actual_qty_gl1_packing.required' => 'GL1 Packing actual quantity is required.',
            'step4.*.actual_qty_gl1_packing.integer' => 'GL1 Packing actual quantity must be a whole number.',
            'step4.*.actual_qty_gl1_packing.min' => 'GL1 Packing actual quantity must be at least 0.',
            'step4.*.actual_qty_gl2_kg.required' => 'GL2 Kg actual quantity is required.',
            'step4.*.actual_qty_gl2_kg.numeric' => 'GL2 Kg actual quantity must be a number.',
            'step4.*.actual_qty_gl2_kg.min' => 'GL2 Kg actual quantity must be at least 0.',
            'step4.*.actual_qty_gl2_packing.required' => 'GL2 Packing actual quantity is required.',
            'step4.*.actual_qty_gl2_packing.integer' => 'GL2 Packing actual quantity must be a whole number.',
            'step4.*.actual_qty_gl2_packing.min' => 'GL2 Packing actual quantity must be at least 0.',
            'step4.*.actual_qty_ta_kg.required' => 'TA Kg actual quantity is required.',
            'step4.*.actual_qty_ta_kg.numeric' => 'TA Kg actual quantity must be a number.',
            'step4.*.actual_qty_ta_kg.min' => 'TA Kg actual quantity must be at least 0.',
            'step4.*.actual_qty_ta_packing.required' => 'TA Packing actual quantity is required.',
            'step4.*.actual_qty_ta_packing.integer' => 'TA Packing actual quantity must be a whole number.',
            'step4.*.actual_qty_ta_packing.min' => 'TA Packing actual quantity must be at least 0.',
            'step4.*.actual_qty_bl_kg.required' => 'BL Kg actual quantity is required.',
            'step4.*.actual_qty_bl_kg.numeric' => 'BL Kg actual quantity must be a number.',
            'step4.*.actual_qty_bl_kg.min' => 'BL Kg actual quantity must be at least 0.',
            'step4.*.actual_qty_bl_packing.required' => 'BL Packing actual quantity is required.',
            'step4.*.actual_qty_bl_packing.integer' => 'BL Packing actual quantity must be a whole number.',
            'step4.*.actual_qty_bl_packing.min' => 'BL Packing actual quantity must be at least 0.',
        ];
    }
}
