<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class StoreDocumentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', \App\Models\Document::class);
    }

    public function rules(): array
    {
        return [
            'document_number' => 'required|string|unique:documents,document_number',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'document_type' => 'required|string',
            'department_id' => 'required|exists:roles,id',
            'physical_location' => 'nullable|array',
            'physical_location.room_no' => 'nullable|string',
            'physical_location.shelf_no' => 'nullable|string',
            'physical_location.folder_no' => 'nullable|string',
            'accessible_departments' => 'nullable|array',
            'accessible_departments.*' => 'exists:roles,id',
        ];
    }
}


















