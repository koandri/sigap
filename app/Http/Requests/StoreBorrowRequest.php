<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Models\Document;
use App\Services\DocumentBorrowService;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Validator;

final class StoreBorrowRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // All authenticated users can submit borrow requests
        return Auth::check();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'document_id' => ['required', 'exists:documents,id'],
            'due_date' => ['nullable', 'date', 'after:today'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'document_id.required' => 'Please select a document to borrow.',
            'document_id.exists' => 'The selected document does not exist.',
            'due_date.after' => 'The due date must be a future date.',
            'notes.max' => 'Notes cannot exceed 1000 characters.',
        ];
    }

    /**
     * Configure the validator instance.
     */
    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            if ($validator->errors()->isNotEmpty()) {
                return;
            }

            $document = Document::find($this->document_id);
            if (! $document) {
                return;
            }

            $borrowService = app(DocumentBorrowService::class);
            $result = $borrowService->canBorrow(Auth::user(), $document);

            if (! $result['can_borrow']) {
                foreach ($result['errors'] as $error) {
                    $validator->errors()->add('document_id', $error);
                }
            }
        });
    }
}
