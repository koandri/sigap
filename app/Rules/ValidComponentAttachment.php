<?php

declare(strict_types=1);

namespace App\Rules;

use App\Models\Asset;
use App\Services\AssetComponentService;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

final class ValidComponentAttachment implements ValidationRule
{
    public function __construct(
        private readonly Asset $parentAsset,
        private readonly ?AssetComponentService $componentService = null
    ) {}

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (!is_numeric($value)) {
            $fail('The component ID must be a valid number.');
            return;
        }

        $component = Asset::find($value);
        if (!$component) {
            $fail('The selected component does not exist.');
            return;
        }

        // Check if parent and component are different
        if ($this->parentAsset->id === $component->id) {
            $fail('An asset cannot be attached to itself.');
            return;
        }

        // Use service to validate if available
        $service = $this->componentService ?? app(AssetComponentService::class);
        $validation = $service->validateComponentAttachment($this->parentAsset, $component);

        if (!$validation['valid']) {
            $fail($validation['message']);
        }
    }
}
