<?php

declare(strict_types=1);

namespace App\Rules;

use App\Models\Document;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

final class UniqueDocumentNumber implements ValidationRule
{
    public function __construct(
        private readonly ?int $excludeId = null
    ) {}

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $query = Document::where('document_number', $value);

        if ($this->excludeId) {
            $query->where('id', '!=', $this->excludeId);
        }

        if ($query->exists()) {
            $fail('The document number has already been taken.');
        }
    }
}
