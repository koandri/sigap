<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Models\Document;
use App\Models\DocumentVersion;
use Illuminate\Foundation\Http\FormRequest;

final class StoreVersionRequest extends FormRequest
{
    
    public function authorize(): bool
    {
        $document = $this->route('document');
        return $this->user()->can('create', [DocumentVersion::class, $document]);
    }

    public function rules(): array
    {
        $document = $this->route('document');
        
        return [
            'creation_method' => 'required|in:scratch,upload,copy',
            'file_type' => 'required_if:creation_method,scratch|nullable|in:docx,xlsx',
            // Use mimes rule with zip included - DOCX/XLSX files are ZIP archives and may be detected as application/zip
            'source_file' => 'required_if:creation_method,upload|file|mimes:docx,xlsx,zip,pdf,jpg,jpeg,png',
            'source_version_id' => [
                'required_if:creation_method,copy',
                function ($attribute, $value, $fail) use ($document) {
                    if ($this->creation_method === 'copy' && !empty($value)) {
                        if (!DocumentVersion::where('id', $value)
                            ->where('document_id', $document->id)
                            ->exists()) {
                            $fail('The selected source version is invalid.');
                        }
                    }
                }
            ],
            'revision_description' => 'nullable|string|max:1000',
            'is_ncr_paper' => 'nullable|boolean',
        ];
    }
    
    public function messages(): array
    {
        return [
            'source_file.mimes' => 'The selected file type is invalid. Allowed types: DOCX, XLSX, PDF, JPG, JPEG, PNG, ZIP',
        ];
    }
}























