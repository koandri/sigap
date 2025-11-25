<?php

declare(strict_types=1);

namespace App\ValueObjects;

use Countable;
use IteratorAggregate;
use Traversable;
use Illuminate\Contracts\Database\Eloquent\Castable;
use Illuminate\Contracts\Database\Eloquent\CastsAttributes;

final class AssetSpecifications implements Castable, Countable, IteratorAggregate
{
    public function __construct(
        public readonly ?string $voltage = null,
        public readonly ?string $power = null,
        public readonly ?string $weight = null,
        public readonly ?array $dimensions = null,
        public readonly array $custom = []
    ) {}

    public static function fromArray(?array $data): ?self
    {
        if ($data === null || empty($data)) {
            return null;
        }

        // Extract known fields
        $voltage = $data['voltage'] ?? $data['Voltage'] ?? null;
        $power = $data['power'] ?? $data['Power'] ?? null;
        $weight = $data['weight'] ?? $data['Weight'] ?? null;
        $dimensions = $data['dimensions'] ?? $data['Dimensions'] ?? null;

        // Everything else goes into custom
        $knownKeys = ['voltage', 'Voltage', 'power', 'Power', 'weight', 'Weight', 'dimensions', 'Dimensions'];
        $custom = array_diff_key($data, array_flip($knownKeys));

        return new self(
            voltage: $voltage,
            power: $power,
            weight: $weight,
            dimensions: $dimensions,
            custom: $custom
        );
    }

    public function toArray(): array
    {
        $base = array_filter([
            'voltage' => $this->voltage,
            'power' => $this->power,
            'weight' => $this->weight,
            'dimensions' => $this->dimensions,
        ], fn($value) => $value !== null);

        return array_merge($base, $this->custom);
    }

    public function count(): int
    {
        return count($this->toArray());
    }

    public function getIterator(): Traversable
    {
        return new \ArrayIterator($this->toArray());
    }

    public function hasElectricalSpecs(): bool
    {
        return $this->voltage !== null || $this->power !== null;
    }

    public function getFormattedWeight(): ?string
    {
        if ($this->weight === null) {
            return null;
        }

        // If weight already has unit, return as-is
        if (preg_match('/\d+\s*(kg|g|lbs|oz)/i', $this->weight)) {
            return $this->weight;
        }

        // Otherwise assume kg
        return $this->weight . ' kg';
    }

    public function getFormattedPower(): ?string
    {
        if ($this->power === null) {
            return null;
        }

        // If power already has unit, return as-is
        if (preg_match('/\d+\s*(W|kW|MW|HP)/i', $this->power)) {
            return $this->power;
        }

        // Otherwise assume W
        return $this->power . ' W';
    }

    public function getFormattedVoltage(): ?string
    {
        if ($this->voltage === null) {
            return null;
        }

        // If voltage already has unit, return as-is
        if (preg_match('/\d+\s*V/i', $this->voltage)) {
            return $this->voltage;
        }

        // Otherwise assume V
        return $this->voltage . ' V';
    }

    public static function castUsing(array $arguments): CastsAttributes
    {
        return new class implements CastsAttributes {
            public function get($model, string $key, $value, array $attributes)
            {
                if ($value === null) {
                    return null;
                }

                $decoded = is_string($value) ? json_decode($value, true) : $value;
                return AssetSpecifications::fromArray($decoded);
            }

            public function set($model, string $key, $value, array $attributes)
            {
                if ($value === null) {
                    return null;
                }

                if ($value instanceof AssetSpecifications) {
                    return json_encode($value->toArray());
                }
                
                if (is_array($value)) {
                    $specs = AssetSpecifications::fromArray($value);
                    return $specs ? json_encode($specs->toArray()) : null;
                }

                if (is_string($value)) {
                    // Try to decode as JSON
                    $decoded = json_decode($value, true);
                    if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                        $specs = AssetSpecifications::fromArray($decoded);
                        return $specs ? json_encode($specs->toArray()) : null;
                    }
                }

                return $value;
            }
        };
    }
}
