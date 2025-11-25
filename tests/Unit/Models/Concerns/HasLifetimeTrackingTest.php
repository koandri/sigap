<?php

declare(strict_types=1);

namespace Tests\Unit\Models\Concerns;

use App\Enums\UsageUnit;
use App\Models\Asset;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class HasLifetimeTrackingTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_calculates_lifetime_percentage_for_time_based_units(): void
    {
        $asset = Asset::factory()->create([
            'expected_lifetime_value' => 100,
            'installed_date' => now()->subDays(50),
            'lifetime_unit' => UsageUnit::Days,
        ]);

        $percentage = $asset->getLifetimePercentage();

        $this->assertEqualsWithDelta(50, $percentage, 1);
    }

    /** @test */
    public function it_returns_null_when_expected_lifetime_not_set(): void
    {
        $asset = Asset::factory()->create([
            'expected_lifetime_value' => null,
        ]);

        $this->assertNull($asset->getLifetimePercentage());
    }

    /** @test */
    public function it_calculates_remaining_lifetime(): void
    {
        $asset = Asset::factory()->create([
            'expected_lifetime_value' => 100,
            'installed_date' => now()->subDays(30),
            'lifetime_unit' => UsageUnit::Days,
        ]);

        $remaining = $asset->getRemainingLifetime();

        $this->assertEqualsWithDelta(70, $remaining, 1);
    }

    /** @test */
    public function it_detects_assets_nearing_end_of_life(): void
    {
        $nearEnd = Asset::factory()->create([
            'expected_lifetime_value' => 100,
            'installed_date' => now()->subDays(85),
            'lifetime_unit' => UsageUnit::Days,
        ]);

        $notNearEnd = Asset::factory()->create([
            'expected_lifetime_value' => 100,
            'installed_date' => now()->subDays(50),
            'lifetime_unit' => UsageUnit::Days,
        ]);

        $this->assertTrue($nearEnd->isNearingEndOfLife());
        $this->assertFalse($notNearEnd->isNearingEndOfLife());
    }

    /** @test */
    public function it_detects_assets_that_exceeded_lifetime(): void
    {
        $exceeded = Asset::factory()->create([
            'expected_lifetime_value' => 100,
            'installed_date' => now()->subDays(105),
            'lifetime_unit' => UsageUnit::Days,
        ]);

        $notExceeded = Asset::factory()->create([
            'expected_lifetime_value' => 100,
            'installed_date' => now()->subDays(50),
            'lifetime_unit' => UsageUnit::Days,
        ]);

        $this->assertTrue($exceeded->hasExceededLifetime());
        $this->assertFalse($notExceeded->hasExceededLifetime());
    }

    /** @test */
    public function it_returns_correct_status_colors(): void
    {
        $green = Asset::factory()->create([
            'expected_lifetime_value' => 100,
            'installed_date' => now()->subDays(30),
            'lifetime_unit' => UsageUnit::Days,
        ]);

        $yellow = Asset::factory()->create([
            'expected_lifetime_value' => 100,
            'installed_date' => now()->subDays(60),
            'lifetime_unit' => UsageUnit::Days,
        ]);

        $orange = Asset::factory()->create([
            'expected_lifetime_value' => 100,
            'installed_date' => now()->subDays(85),
            'lifetime_unit' => UsageUnit::Days,
        ]);

        $red = Asset::factory()->create([
            'expected_lifetime_value' => 100,
            'installed_date' => now()->subDays(105),
            'lifetime_unit' => UsageUnit::Days,
        ]);

        $this->assertEquals('green', $green->getLifetimeStatusColor());
        $this->assertEquals('yellow', $yellow->getLifetimeStatusColor());
        $this->assertEquals('orange', $orange->getLifetimeStatusColor());
        $this->assertEquals('red', $red->getLifetimeStatusColor());
    }

    /** @test */
    public function it_formats_lifetime_percentage(): void
    {
        $asset = Asset::factory()->create([
            'expected_lifetime_value' => 100,
            'installed_date' => now()->subDays(50),
            'lifetime_unit' => UsageUnit::Days,
        ]);

        $formatted = $asset->getFormattedLifetimePercentage();

        $this->assertStringContainsString('%', $formatted);
        $this->assertStringContainsString('50', $formatted);
    }

    /** @test */
    public function it_formats_remaining_lifetime_with_unit(): void
    {
        $asset = Asset::factory()->create([
            'expected_lifetime_value' => 100,
            'installed_date' => now()->subDays(30),
            'lifetime_unit' => UsageUnit::Days,
        ]);

        $formatted = $asset->getFormattedRemainingLifetime();

        $this->assertStringContainsString('70', $formatted);
        $this->assertStringContainsString('Days', $formatted);
    }

    /** @test */
    public function it_calculates_estimated_end_of_life_date(): void
    {
        $asset = Asset::factory()->create([
            'expected_lifetime_value' => 100,
            'installed_date' => now()->subDays(30),
            'lifetime_unit' => UsageUnit::Days,
        ]);

        $endDate = $asset->getEstimatedEndOfLifeDate();

        $this->assertNotNull($endDate);
        // End date should be 70 days in the future (100 total - 30 already passed)
        $this->assertEqualsWithDelta(70, now()->diffInDays($endDate), 1);
    }

    /** @test */
    public function it_uses_actual_lifetime_for_disposed_assets(): void
    {
        $asset = Asset::factory()->create([
            'expected_lifetime_value' => 100,
            'installed_date' => now()->subDays(30),
            'disposed_date' => now(),
            'actual_lifetime_value' => 80,
            'lifetime_unit' => UsageUnit::Days,
        ]);

        $percentage = $asset->getLifetimePercentage();

        $this->assertEquals(80, $percentage);
    }
}
