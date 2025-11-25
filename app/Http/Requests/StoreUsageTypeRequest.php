<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class StoreUsageTypeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Authorization handled by controller/policy
    }

    public function rules(): array
    {
        $category = $this->route('category');

        return [
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('asset_category_usage_types', 'name')
                    ->where('asset_category_id', $category->id)
                    ->ignore($this->route('usageType')),
            ],
            'description' => 'nullable|string|max:1000',
            'lifetime_unit' => 'required|string|in:days,kilometers,machine_hours,cycles',
            'expected_average_lifetime' => 'nullable|numeric|min:0',
            'is_active' => 'nullable|boolean',
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Name is required.',
            'name.max' => 'Name cannot exceed 255 characters.',
            'name.unique' => 'A usage type with this name already exists for this category.',
            'description.max' => 'Description cannot exceed 1000 characters.',
            'lifetime_unit.required' => 'Lifetime unit is required.',
            'lifetime_unit.in' => 'Invalid lifetime unit.',
            'expected_average_lifetime.numeric' => 'Expected average lifetime must be a number.',
            'expected_average_lifetime.min' => 'Expected average lifetime must be at least 0.',
        ];
    }
}
