<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class RecordProductionActualStep5Request extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('manufacturing.production-plans.record-actuals');
    }

    public function rules(): array
    {
        return [
            'step5' => 'required|array|min:1',
            'step5.*.production_plan_step5_id' => 'nullable|exists:production_plan_step5,id',
            'step5.*.pack_sku_id' => 'required_without:step5.*.production_plan_step5_id|exists:items,id',
            'step5.*.packing_material_item_id' => 'required_without:step5.*.production_plan_step5_id|exists:items,id',
            'step5.*.actual_quantity_total' => 'required|integer|min:0',
        ];
    }

    public function messages(): array
    {
        return [
            'step5.required' => 'Step 5 data is required.',
            'step5.array' => 'Step 5 data must be an array.',
            'step5.min' => 'At least one step 5 entry is required.',
            'step5.*.production_plan_step5_id.exists' => 'Selected production plan step 5 does not exist.',
            'step5.*.pack_sku_id.required_without' => 'Pack SKU is required when creating a new item.',
            'step5.*.pack_sku_id.exists' => 'Selected pack SKU does not exist.',
            'step5.*.packing_material_item_id.required_without' => 'Packing material item is required when creating a new item.',
            'step5.*.packing_material_item_id.exists' => 'Selected packing material item does not exist.',
            'step5.*.actual_quantity_total.required' => 'Actual total quantity is required.',
            'step5.*.actual_quantity_total.integer' => 'Actual total quantity must be a whole number.',
            'step5.*.actual_quantity_total.min' => 'Actual total quantity must be at least 0.',
        ];
    }
}
