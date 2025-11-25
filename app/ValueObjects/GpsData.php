<?php

declare(strict_types=1);

namespace App\ValueObjects;

use Illuminate\Contracts\Database\Eloquent\Castable;
use Illuminate\Contracts\Database\Eloquent\CastsAttributes;

final class GpsData implements Castable
{
    public function __construct(
        public readonly float $latitude,
        public readonly float $longitude,
        public readonly ?float $altitude = null,
        public readonly ?float $accuracy = null
    ) {}

    public static function fromArray(?array $data): ?self
    {
        if ($data === null || !isset($data['latitude'], $data['longitude'])) {
            return null;
        }

        return new self(
            latitude: (float) $data['latitude'],
            longitude: (float) $data['longitude'],
            altitude: isset($data['altitude']) ? (float) $data['altitude'] : null,
            accuracy: isset($data['accuracy']) ? (float) $data['accuracy'] : null
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'latitude' => $this->latitude,
            'longitude' => $this->longitude,
            'altitude' => $this->altitude,
            'accuracy' => $this->accuracy,
        ], fn($value) => $value !== null);
    }

    public function getGoogleMapsUrl(): string
    {
        return "https://www.google.com/maps?q={$this->latitude},{$this->longitude}";
    }

    public function getCoordinatesString(): string
    {
        return sprintf('%.6f, %.6f', $this->latitude, $this->longitude);
    }

    /**
     * Calculate distance to another GPS point using Haversine formula.
     * Returns distance in kilometers.
     */
    public function distanceTo(GpsData $other): float
    {
        $earthRadius = 6371; // km

        $latDelta = deg2rad($other->latitude - $this->latitude);
        $lonDelta = deg2rad($other->longitude - $this->longitude);

        $a = sin($latDelta / 2) * sin($latDelta / 2) +
             cos(deg2rad($this->latitude)) * cos(deg2rad($other->latitude)) *
             sin($lonDelta / 2) * sin($lonDelta / 2);

        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return $earthRadius * $c;
    }

    /**
     * Check if coordinates are within a bounding box.
     */
    public function isWithinBounds(float $minLat, float $maxLat, float $minLon, float $maxLon): bool
    {
        return $this->latitude >= $minLat 
            && $this->latitude <= $maxLat
            && $this->longitude >= $minLon
            && $this->longitude <= $maxLon;
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
                return GpsData::fromArray($decoded);
            }

            public function set($model, string $key, $value, array $attributes)
            {
                if ($value === null) {
                    return null;
                }

                if ($value instanceof GpsData) {
                    return json_encode($value->toArray());
                }
                
                if (is_array($value)) {
                    $gps = GpsData::fromArray($value);
                    return $gps ? json_encode($gps->toArray()) : null;
                }

                return $value;
            }
        };
    }
}
