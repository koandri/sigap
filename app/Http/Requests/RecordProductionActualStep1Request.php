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
            'step1.*.production_plan_step1_id' => 'nullable|exists:production_plan_step1,id',
            'step1.*.dough_item_id' => 'required_without:step1.*.production_plan_step1_id|exists:items,id',
            'step1.*.recipe_id' => 'required_without:step1.*.production_plan_step1_id|exists:recipes,id',
            'step1.*.ingredients' => 'required_without:step1.*.production_plan_step1_id|array',
            'step1.*.ingredients.*.ingredient_item_id' => 'required_with:step1.*.ingredients|exists:items,id',
            'step1.*.ingredients.*.quantity' => 'required_with:step1.*.ingredients|numeric|min:0.001',
            'step1.*.ingredients.*.unit' => 'nullable|string|max:15',
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
            'step1.*.production_plan_step1_id.exists' => 'Selected production plan step 1 does not exist.',
            'step1.*.dough_item_id.required_without' => 'Dough item is required when creating a new item.',
            'step1.*.dough_item_id.exists' => 'Selected dough item does not exist.',
            'step1.*.recipe_id.required_without' => 'Recipe is required when creating a new item.',
            'step1.*.recipe_id.exists' => 'Selected recipe does not exist.',
            'step1.*.ingredients.required_without' => 'Ingredients are required when creating a new item.',
            'step1.*.ingredients.array' => 'Ingredients must be an array.',
            'step1.*.ingredients.*.ingredient_item_id.required_with' => 'Ingredient item is required.',
            'step1.*.ingredients.*.ingredient_item_id.exists' => 'Selected ingredient item does not exist.',
            'step1.*.ingredients.*.quantity.required_with' => 'Ingredient quantity is required.',
            'step1.*.ingredients.*.quantity.numeric' => 'Ingredient quantity must be a number.',
            'step1.*.ingredients.*.quantity.min' => 'Ingredient quantity must be at least 0.001.',
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
