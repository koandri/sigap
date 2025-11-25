<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class AttachComponentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Authorization handled by controller/policy
    }

    public function rules(): array
    {
        return [
            'component_id' => 'required|exists:assets,id|different:parent_asset_id',
            'component_type' => 'required|string|in:consumable,replaceable,integral',
            'installed_date' => 'nullable|date',
            'installed_usage_value' => 'nullable|numeric|min:0',
            'installation_notes' => 'nullable|string|max:1000',
        ];
    }

    public function messages(): array
    {
        return [
            'component_id.required' => 'Component is required.',
            'component_id.exists' => 'Selected component does not exist.',
            'component_id.different' => 'Component cannot be the same as parent asset.',
            'component_type.required' => 'Component type is required.',
            'component_type.in' => 'Invalid component type.',
            'installed_date.date' => 'Installed date must be a valid date.',
            'installed_usage_value.numeric' => 'Installed usage value must be a number.',
            'installed_usage_value.min' => 'Installed usage value must be at least 0.',
            'installation_notes.max' => 'Installation notes cannot exceed 1000 characters.',
        ];
    }
}
