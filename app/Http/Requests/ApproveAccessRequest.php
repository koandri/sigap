<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Models\DocumentAccessRequest;
use Illuminate\Foundation\Http\FormRequest;

final class ApproveAccessRequest extends FormRequest
{
    public function authorize(): bool
    {
        $accessRequest = $this->route('request');
        return $this->user()->can('approve', $accessRequest);
    }

    public function rules(): array
    {
        return [
            'approved_access_type' => 'required|string',
            'approved_expiry_date' => 'nullable|date|after:now',
            'notes' => 'nullable|string|max:1000',
        ];
    }

    public function messages(): array
    {
        return [
            'approved_access_type.required' => 'Please select an access type.',
            'approved_expiry_date.after' => 'The expiry date must be in the future.',
        ];
    }
}





















