<?php

declare(strict_types=1);

namespace Tests\Unit\ValueObjects;

use App\ValueObjects\GpsData;
use Tests\TestCase;

final class GpsDataTest extends TestCase
{
    /** @test */
    public function it_creates_from_array(): void
    {
        $data = [
            'latitude' => -6.200000,
            'longitude' => 106.816666,
            'altitude' => 8.0,
            'accuracy' => 10.5,
        ];

        $gps = GpsData::fromArray($data);

        $this->assertEquals(-6.200000, $gps->latitude);
        $this->assertEquals(106.816666, $gps->longitude);
        $this->assertEquals(8.0, $gps->altitude);
        $this->assertEquals(10.5, $gps->accuracy);
    }

    /** @test */
    public function it_requires_latitude_and_longitude(): void
    {
        $this->assertNull(GpsData::fromArray(null));
        $this->assertNull(GpsData::fromArray([]));
        $this->assertNull(GpsData::fromArray(['latitude' => -6.2]));
        $this->assertNull(GpsData::fromArray(['longitude' => 106.8]));
    }

    /** @test */
    public function it_works_with_minimal_data(): void
    {
        $data = [
            'latitude' => -6.200000,
            'longitude' => 106.816666,
        ];

        $gps = GpsData::fromArray($data);

        $this->assertEquals(-6.200000, $gps->latitude);
        $this->assertEquals(106.816666, $gps->longitude);
        $this->assertNull($gps->altitude);
        $this->assertNull($gps->accuracy);
    }

    /** @test */
    public function it_converts_to_array(): void
    {
        $gps = new GpsData(
            latitude: -6.200000,
            longitude: 106.816666,
            altitude: 8.0,
            accuracy: 10.5
        );

        $array = $gps->toArray();

        $this->assertEquals(-6.200000, $array['latitude']);
        $this->assertEquals(106.816666, $array['longitude']);
        $this->assertEquals(8.0, $array['altitude']);
        $this->assertEquals(10.5, $array['accuracy']);
    }

    /** @test */
    public function it_generates_google_maps_url(): void
    {
        $gps = new GpsData(latitude: -6.200000, longitude: 106.816666);

        $url = $gps->getGoogleMapsUrl();

        $this->assertEquals('https://www.google.com/maps?q=-6.2,106.816666', $url);
    }

    /** @test */
    public function it_formats_coordinates_string(): void
    {
        $gps = new GpsData(latitude: -6.200000, longitude: 106.816666);

        $coords = $gps->getCoordinatesString();

        $this->assertEquals('-6.200000, 106.816666', $coords);
    }

    /** @test */
    public function it_calculates_distance_between_points(): void
    {
        // Jakarta
        $jakarta = new GpsData(latitude: -6.200000, longitude: 106.816666);
        // Bandung (approximately 120km from Jakarta)
        $bandung = new GpsData(latitude: -6.914744, longitude: 107.609810);

        $distance = $jakarta->distanceTo($bandung);

        // Distance should be approximately 120km (allowing 10km margin)
        $this->assertEqualsWithDelta(120, $distance, 10);
    }

    /** @test */
    public function it_checks_if_within_bounds(): void
    {
        $gps = new GpsData(latitude: -6.200000, longitude: 106.816666);

        // Jakarta bounding box
        $this->assertTrue($gps->isWithinBounds(-6.5, -6.0, 106.5, 107.0));
        
        // Outside Jakarta
        $this->assertFalse($gps->isWithinBounds(-7.0, -6.5, 106.5, 107.0));
    }

    /** @test */
    public function it_casts_string_coordinates_to_float(): void
    {
        $data = [
            'latitude' => '-6.200000',
            'longitude' => '106.816666',
            'altitude' => '8.0',
            'accuracy' => '10.5',
        ];

        $gps = GpsData::fromArray($data);

        $this->assertIsFloat($gps->latitude);
        $this->assertIsFloat($gps->longitude);
        $this->assertIsFloat($gps->altitude);
        $this->assertIsFloat($gps->accuracy);
    }
}
