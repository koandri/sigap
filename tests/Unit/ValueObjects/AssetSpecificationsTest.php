<?php

declare(strict_types=1);

namespace Tests\Unit\ValueObjects;

use App\ValueObjects\AssetSpecifications;
use Tests\TestCase;

final class AssetSpecificationsTest extends TestCase
{
    /** @test */
    public function it_creates_from_array(): void
    {
        $data = [
            'voltage' => '220V',
            'power' => '1000W',
            'weight' => '50kg',
            'custom_field' => 'custom_value',
        ];

        $specs = AssetSpecifications::fromArray($data);

        $this->assertEquals('220V', $specs->voltage);
        $this->assertEquals('1000W', $specs->power);
        $this->assertEquals('50kg', $specs->weight);
        $this->assertEquals('custom_value', $specs->custom['custom_field']);
    }

    /** @test */
    public function it_returns_null_for_empty_array(): void
    {
        $this->assertNull(AssetSpecifications::fromArray(null));
        $this->assertNull(AssetSpecifications::fromArray([]));
    }

    /** @test */
    public function it_converts_to_array(): void
    {
        $specs = new AssetSpecifications(
            voltage: '220V',
            power: '1000W',
            weight: '50kg',
            custom: ['brand' => 'Acme']
        );

        $array = $specs->toArray();

        $this->assertEquals('220V', $array['voltage']);
        $this->assertEquals('1000W', $array['power']);
        $this->assertEquals('50kg', $array['weight']);
        $this->assertEquals('Acme', $array['brand']);
    }

    /** @test */
    public function it_detects_electrical_specs(): void
    {
        $withElectrical = new AssetSpecifications(voltage: '220V');
        $withoutElectrical = new AssetSpecifications(weight: '50kg');

        $this->assertTrue($withElectrical->hasElectricalSpecs());
        $this->assertFalse($withoutElectrical->hasElectricalSpecs());
    }

    /** @test */
    public function it_formats_weight_with_unit(): void
    {
        $withUnit = new AssetSpecifications(weight: '50kg');
        $withoutUnit = new AssetSpecifications(weight: '50');

        $this->assertEquals('50kg', $withUnit->getFormattedWeight());
        $this->assertEquals('50 kg', $withoutUnit->getFormattedWeight());
    }

    /** @test */
    public function it_formats_power_with_unit(): void
    {
        $withUnit = new AssetSpecifications(power: '1000W');
        $withoutUnit = new AssetSpecifications(power: '1000');

        $this->assertEquals('1000W', $withUnit->getFormattedPower());
        $this->assertEquals('1000 W', $withoutUnit->getFormattedPower());
    }

    /** @test */
    public function it_formats_voltage_with_unit(): void
    {
        $withUnit = new AssetSpecifications(voltage: '220V');
        $withoutUnit = new AssetSpecifications(voltage: '220');

        $this->assertEquals('220V', $withUnit->getFormattedVoltage());
        $this->assertEquals('220 V', $withoutUnit->getFormattedVoltage());
    }

    /** @test */
    public function it_handles_case_insensitive_keys(): void
    {
        $data = [
            'Voltage' => '220V',
            'Power' => '1000W',
            'Weight' => '50kg',
        ];

        $specs = AssetSpecifications::fromArray($data);

        $this->assertEquals('220V', $specs->voltage);
        $this->assertEquals('1000W', $specs->power);
        $this->assertEquals('50kg', $specs->weight);
    }

    /** @test */
    public function it_preserves_custom_fields(): void
    {
        $data = [
            'voltage' => '220V',
            'manufacturer' => 'Acme Corp',
            'model_number' => 'X-1000',
            'color' => 'blue',
        ];

        $specs = AssetSpecifications::fromArray($data);

        $this->assertEquals('Acme Corp', $specs->custom['manufacturer']);
        $this->assertEquals('X-1000', $specs->custom['model_number']);
        $this->assertEquals('blue', $specs->custom['color']);
    }
}
