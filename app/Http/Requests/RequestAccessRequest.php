<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Enums\AccessType;
use App\Models\Document;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class RequestAccessRequest extends FormRequest
{
    public function authorize(): bool
    {
        $document = $this->route('document');
        return $this->user()->can('view', $document);
    }

    public function rules(): array
    {
        $rules = [
            'access_type' => ['required', 'string', Rule::enum(AccessType::class)],
            'reason' => 'required|string|max:1000',
        ];

        // Make expiry date required for Multiple Access
        if ($this->input('access_type') === AccessType::Multiple->value) {
            $rules['requested_expiry_date'] = 'required|date|after:now';
        } else {
            $rules['requested_expiry_date'] = 'nullable|date|after:now';
        }

        return $rules;
    }

    public function messages(): array
    {
        return [
            'access_type.required' => 'Please select an access type.',
            'requested_expiry_date.required' => 'The expiry date is required for Multiple Access.',
            'requested_expiry_date.after' => 'The expiry date must be in the future.',
            'reason.required' => 'Please provide a reason for requesting access.',
        ];
    }
}























