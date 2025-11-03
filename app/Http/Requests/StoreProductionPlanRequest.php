<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class StoreProductionPlanRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', \App\Models\ProductionPlan::class) 
            || $this->user()->hasPermissionTo('manufacturing.production-plans.create');
    }

    public function rules(): array
    {
        return [
            'plan_date' => 'required|date',
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

