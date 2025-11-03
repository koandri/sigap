<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class UpdateProductionPlanRequest extends FormRequest
{
    public function authorize(): bool
    {
        $plan = $this->route('production-plan') ?? $this->route('productionPlan');
        
        if (!$plan) {
            return false;
        }

        // Only allow update if plan is in draft status
        if (!$plan->canBeEdited()) {
            return false;
        }

        return $this->user()->can('update', $plan)
            || $this->user()->hasPermissionTo('manufacturing.production-plans.edit');
    }

    public function rules(): array
    {
        return [
            'plan_date' => 'sometimes|required|date',
            'notes' => 'nullable|string|max:1000',
            'status' => 'nullable|string|in:draft,approved',
        ];
    }

    public function messages(): array
    {
        return [
            'plan_date.required' => 'Plan date is required.',
            'plan_date.date' => 'Plan date must be a valid date.',
            'notes.max' => 'Notes cannot exceed 1000 characters.',
            'status.in' => 'Status must be either draft or approved.',
        ];
    }
}

