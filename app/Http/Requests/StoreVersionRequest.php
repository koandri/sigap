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
            'file_type' => 'required_if:creation_method,scratch|in:docx,xlsx',
            'source_file' => 'required_if:creation_method,upload|file|mimetypes:application/pdf,application/vnd.openxmlformats-officedocument.wordprocessingml.document,application/vnd.openxmlformats-officedocument.spreadsheetml.sheet,image/jpeg,image/png,application/zip',
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
}























