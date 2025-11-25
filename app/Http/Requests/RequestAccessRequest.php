<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Models\Document;
use Illuminate\Foundation\Http\FormRequest;

final class RequestAccessRequest extends FormRequest
{
    public function authorize(): bool
    {
        $document = $this->route('document');
        return $this->user()->can('view', $document);
    }

    public function rules(): array
    {
        return [
            'access_type' => 'required|string',
            'requested_expiry_date' => 'nullable|date|after:now',
            'reason' => 'required|string|max:1000',
        ];
    }

    public function messages(): array
    {
        return [
            'access_type.required' => 'Please select an access type.',
            'requested_expiry_date.after' => 'The expiry date must be in the future.',
            'reason.required' => 'Please provide a reason for requesting access.',
        ];
    }
}























