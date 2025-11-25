<?php

declare(strict_types=1);

namespace Tests\Unit\Models;

use App\Enums\ComponentType;
use App\Models\Asset;
use App\Models\AssetComponent;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class AssetComponentTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_belongs_to_parent_asset(): void
    {
        $parent = Asset::factory()->create();
        $component = AssetComponent::factory()->create(['parent_asset_id' => $parent->id]);

        $this->assertInstanceOf(Asset::class, $component->parentAsset);
        $this->assertEquals($parent->id, $component->parentAsset->id);
    }

    /** @test */
    public function it_belongs_to_component_asset(): void
    {
        $componentAsset = Asset::factory()->create();
        $component = AssetComponent::factory()->create(['component_asset_id' => $componentAsset->id]);

        $this->assertInstanceOf(Asset::class, $component->componentAsset);
        $this->assertEquals($componentAsset->id, $component->componentAsset->id);
    }

    /** @test */
    public function scope_active_returns_only_active_components(): void
    {
        AssetComponent::factory()->active()->create();
        AssetComponent::factory()->removed()->create();

        $active = AssetComponent::active()->get();

        $this->assertCount(1, $active);
        $this->assertNull($active->first()->removed_date);
    }

    /** @test */
    public function scope_removed_returns_only_removed_components(): void
    {
        AssetComponent::factory()->active()->create();
        AssetComponent::factory()->removed()->create();

        $removed = AssetComponent::removed()->get();

        $this->assertCount(1, $removed);
        $this->assertNotNull($removed->first()->removed_date);
    }

    /** @test */
    public function it_checks_if_component_is_active(): void
    {
        $active = AssetComponent::factory()->active()->create();
        $removed = AssetComponent::factory()->removed()->create();

        $this->assertTrue($active->isActive());
        $this->assertFalse($removed->isActive());
    }

    /** @test */
    public function it_calculates_installed_duration(): void
    {
        $component = AssetComponent::factory()->create([
            'installed_date' => now()->subDays(30),
            'removed_date' => null,
        ]);

        $duration = $component->getInstalledDuration();

        $this->assertEqualsWithDelta(30, $duration, 1);
    }

    /** @test */
    public function asset_has_active_components_relationship(): void
    {
        $parent = Asset::factory()->create();
        AssetComponent::factory()->count(2)->active()->create(['parent_asset_id' => $parent->id]);
        AssetComponent::factory()->removed()->create(['parent_asset_id' => $parent->id]);

        $components = $parent->activeComponents;

        $this->assertCount(2, $components);
    }

    /** @test */
    public function asset_can_check_if_it_has_components(): void
    {
        $withComponents = Asset::factory()->create();
        $withoutComponents = Asset::factory()->create();
        
        AssetComponent::factory()->active()->create(['parent_asset_id' => $withComponents->id]);

        $this->assertTrue($withComponents->hasComponents());
        $this->assertFalse($withoutComponents->hasComponents());
    }

    /** @test */
    public function asset_can_check_if_it_is_a_component(): void
    {
        $component = Asset::factory()->create();
        $notComponent = Asset::factory()->create();
        
        AssetComponent::factory()->active()->create(['component_asset_id' => $component->id]);

        $this->assertTrue($component->isComponent());
        $this->assertFalse($notComponent->isComponent());
    }

    /** @test */
    public function asset_can_get_parent_assets(): void
    {
        $component = Asset::factory()->create();
        $parent1 = Asset::factory()->create();
        $parent2 = Asset::factory()->create();
        
        AssetComponent::factory()->active()->create([
            'parent_asset_id' => $parent1->id,
            'component_asset_id' => $component->id,
        ]);
        AssetComponent::factory()->active()->create([
            'parent_asset_id' => $parent2->id,
            'component_asset_id' => $component->id,
        ]);

        $parents = $component->getParentAssets();

        $this->assertCount(2, $parents);
        $this->assertTrue($parents->pluck('id')->contains($parent1->id));
        $this->assertTrue($parents->pluck('id')->contains($parent2->id));
    }

    /** @test */
    public function asset_can_get_component_assets(): void
    {
        $parent = Asset::factory()->create();
        $component1 = Asset::factory()->create();
        $component2 = Asset::factory()->create();
        
        AssetComponent::factory()->active()->create([
            'parent_asset_id' => $parent->id,
            'component_asset_id' => $component1->id,
        ]);
        AssetComponent::factory()->active()->create([
            'parent_asset_id' => $parent->id,
            'component_asset_id' => $component2->id,
        ]);

        $components = $parent->getComponentAssets();

        $this->assertCount(2, $components);
        $this->assertTrue($components->pluck('id')->contains($component1->id));
        $this->assertTrue($components->pluck('id')->contains($component2->id));
    }
}
