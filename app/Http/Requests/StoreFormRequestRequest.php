<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class StoreFormRequestRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Authorization handled by controller/policy
    }

    public function rules(): array
    {
        return [
            'forms' => 'required|array|min:1',
            'forms.*.document_version_id' => 'required|exists:document_versions,id',
            'forms.*.quantity' => 'required|integer|min:1|max:100',
        ];
    }

    public function messages(): array
    {
        return [
            'forms.required' => 'Please select at least one form to request.',
            'forms.min' => 'Please select at least one form to request.',
            'forms.*.document_version_id.required' => 'Document version is required.',
            'forms.*.document_version_id.exists' => 'Selected document version is invalid.',
            'forms.*.quantity.required' => 'Quantity is required.',
            'forms.*.quantity.min' => 'Quantity must be at least 1.',
            'forms.*.quantity.max' => 'Quantity cannot exceed 100.',
        ];
    }
}

