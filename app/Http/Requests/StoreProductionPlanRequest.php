<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class StoreProductionPlanRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Super Admin can always create (bypass all checks)
        if ($this->user()->hasRole('Super Admin')) {
            return true;
        }

        return $this->user()->can('create', \App\Models\ProductionPlan::class) 
            || $this->user()->hasPermissionTo('manufacturing.production-plans.create');
    }

    public function rules(): array
    {
        return [
            'plan_date' => 'required|date',
            'production_start_date' => 'nullable|date|after:plan_date',
            'ready_date' => 'nullable|date|after:production_start_date',
            'notes' => 'nullable|string|max:1000',
            'status' => 'nullable|string|in:draft,approved',
            'step1' => 'nullable|array',
            'step1.*.dough_item_id' => 'required|exists:items,id',
            'step1.*.recipe_id' => 'required|exists:recipes,id',
            'step1.*.qty_gl1' => 'required|integer|min:0',
            'step1.*.qty_gl2' => 'required|integer|min:0',
            'step1.*.qty_ta' => 'required|integer|min:0',
            'step1.*.qty_bl' => 'required|integer|min:0',
            'step1.*.ingredients' => 'nullable|array',
            'step1.*.ingredients.*.ingredient_item_id' => 'required_with:step1.*.ingredients|exists:items,id',
            'step1.*.ingredients.*.quantity' => 'required_with:step1.*.ingredients|numeric|min:0.001',
            'step1.*.ingredients.*.unit' => 'nullable|string|max:15',
        ];
    }

    public function messages(): array
    {
        return [
            'plan_date.required' => 'Plan date is required.',
            'plan_date.date' => 'Plan date must be a valid date.',
            'production_start_date.date' => 'Production start date must be a valid date.',
            'production_start_date.after' => 'Production start date must be after the plan date.',
            'ready_date.date' => 'Ready date must be a valid date.',
            'ready_date.after' => 'Ready date must be after the production start date.',
            'notes.max' => 'Notes cannot exceed 1000 characters.',
            'status.in' => 'Status must be either draft or approved.',
            'step1.*.dough_item_id.required' => 'Dough item is required for each step 1 entry.',
            'step1.*.dough_item_id.exists' => 'Selected dough item does not exist.',
            'step1.*.recipe_id.required' => 'A recipe must be selected for each dough item.',
            'step1.*.recipe_id.exists' => 'Selected recipe does not exist.',
            'step1.*.qty_gl1.required' => 'GL1 quantity is required.',
            'step1.*.qty_gl1.integer' => 'GL1 quantity must be a whole number.',
            'step1.*.qty_gl1.min' => 'GL1 quantity must be at least 0.',
            'step1.*.qty_gl2.required' => 'GL2 quantity is required.',
            'step1.*.qty_gl2.integer' => 'GL2 quantity must be a whole number.',
            'step1.*.qty_gl2.min' => 'GL2 quantity must be at least 0.',
            'step1.*.qty_ta.required' => 'TA quantity is required.',
            'step1.*.qty_ta.integer' => 'TA quantity must be a whole number.',
            'step1.*.qty_ta.min' => 'TA quantity must be at least 0.',
            'step1.*.qty_bl.required' => 'BL quantity is required.',
            'step1.*.qty_bl.integer' => 'BL quantity must be a whole number.',
            'step1.*.qty_bl.min' => 'BL quantity must be at least 0.',
            'step1.*.ingredients.*.ingredient_item_id.required_with' => 'Ingredient item is required.',
            'step1.*.ingredients.*.ingredient_item_id.exists' => 'Selected ingredient item does not exist.',
            'step1.*.ingredients.*.quantity.required_with' => 'Ingredient quantity is required.',
            'step1.*.ingredients.*.quantity.numeric' => 'Ingredient quantity must be a number.',
            'step1.*.ingredients.*.quantity.min' => 'Ingredient quantity must be at least 0.001.',
            'step1.*.ingredients.*.unit.max' => 'Ingredient unit cannot exceed 15 characters.',
        ];
    }
}

