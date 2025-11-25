<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class DetachComponentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Authorization handled by controller/policy
    }

    public function rules(): array
    {
        $component = $this->route('component');
        $installedUsageValue = $component?->installed_usage_value;
        $disposeAsset = $this->boolean('dispose_asset', false);

        $rules = [
            'disposed_date' => 'nullable|date',
            'disposed_usage_value' => 'nullable|numeric|min:0',
            'dispose_asset' => 'required|boolean',
            'notes' => 'nullable|string|max:1000',
        ];

        // Only require disposed_usage_value validation when disposing
        if ($disposeAsset && $installedUsageValue !== null) {
            $rules['disposed_usage_value'] .= '|required|gte:' . $installedUsageValue;
        } elseif ($installedUsageValue !== null) {
            $rules['disposed_usage_value'] .= '|gte:' . $installedUsageValue;
        }

        return $rules;
    }

    public function messages(): array
    {
        return [
            'disposed_date.date' => 'Disposed date must be a valid date.',
            'disposed_usage_value.required' => 'End usage value is required when disposing the component.',
            'disposed_usage_value.numeric' => 'Disposed usage value must be a number.',
            'disposed_usage_value.min' => 'Disposed usage value must be at least 0.',
            'disposed_usage_value.gte' => 'Disposed usage value must be greater than or equal to installed usage value.',
            'dispose_asset.required' => 'Please specify whether to dispose the asset.',
            'dispose_asset.boolean' => 'Dispose asset must be true or false.',
            'notes.max' => 'Notes cannot exceed 1000 characters.',
        ];
    }
}
