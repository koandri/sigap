<?php

declare(strict_types=1);

namespace Tests\Unit\Models;

use App\Enums\ComponentType;
use App\Enums\FileCategory;
use App\Enums\UsageUnit;
use App\Models\Asset;
use App\Models\AssetCategory;
use App\Models\File;
use App\Models\Department;
use App\Models\Location;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class AssetTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_belongs_to_asset_category(): void
    {
        $category = AssetCategory::factory()->create();
        $asset = Asset::factory()->create(['asset_category_id' => $category->id]);

        $this->assertInstanceOf(AssetCategory::class, $asset->assetCategory);
        $this->assertEquals($category->id, $asset->assetCategory->id);
    }

    /** @test */
    public function it_belongs_to_location(): void
    {
        $location = Location::factory()->create();
        $asset = Asset::factory()->create(['location_id' => $location->id]);

        $this->assertInstanceOf(Location::class, $asset->location);
        $this->assertEquals($location->id, $asset->location->id);
    }

    /** @test */
    public function it_belongs_to_department(): void
    {
        $department = Department::factory()->create();
        $asset = Asset::factory()->create(['department_id' => $department->id]);

        $this->assertInstanceOf(Department::class, $asset->department);
        $this->assertEquals($department->id, $asset->department->id);
    }

    /** @test */
    public function it_belongs_to_user(): void
    {
        $user = User::factory()->create();
        $asset = Asset::factory()->create(['user_id' => $user->id]);

        $this->assertInstanceOf(User::class, $asset->user);
        $this->assertEquals($user->id, $asset->user->id);
    }

    /** @test */
    public function it_has_many_photos(): void
    {
        $asset = Asset::factory()->create();
        File::factory()->photo()->count(3)->create([
            'fileable_type' => Asset::class,
            'fileable_id' => $asset->id,
        ]);

        $this->assertCount(3, $asset->photos);
        $this->assertInstanceOf(File::class, $asset->photos->first());
    }

    /** @test */
    public function it_can_identify_component_assets(): void
    {
        $parent = Asset::factory()->create();
        $component = Asset::factory()->create();
        
        \App\Models\AssetComponent::factory()->active()->create([
            'parent_asset_id' => $parent->id,
            'component_asset_id' => $component->id,
        ]);

        $this->assertTrue($component->isComponent());
        $this->assertFalse($parent->isComponent());
    }

    /** @test */
    public function it_can_identify_assets_with_components(): void
    {
        $parent = Asset::factory()->create();
        $component = Asset::factory()->create();
        
        \App\Models\AssetComponent::factory()->active()->create([
            'parent_asset_id' => $parent->id,
            'component_asset_id' => $component->id,
        ]);

        $this->assertTrue($parent->hasComponents());
    }

    /** @test */
    public function scope_active_returns_only_active_assets(): void
    {
        Asset::factory()->create(['status' => 'operational']);
        Asset::factory()->create(['status' => 'disposed']);

        $activeAssets = Asset::active()->get();

        $this->assertCount(1, $activeAssets);
        $this->assertNotEquals('disposed', $activeAssets->first()->status);
    }

    /** @test */
    public function scope_disposed_returns_disposed_assets(): void
    {
        Asset::factory()->create(['status' => 'disposed']);
        Asset::factory()->create(['status' => 'operational']);

        $disposedAssets = Asset::disposed()->get();

        $this->assertCount(1, $disposedAssets->count());
        $this->assertEquals('disposed', $disposedAssets->first()->status);
    }

    /** @test */
    public function scope_by_category_filters_by_category_id(): void
    {
        $category1 = AssetCategory::factory()->create();
        $category2 = AssetCategory::factory()->create();
        
        Asset::factory()->count(2)->create(['asset_category_id' => $category1->id]);
        Asset::factory()->create(['asset_category_id' => $category2->id]);

        $filtered = Asset::byCategory($category1->id)->get();

        $this->assertCount(2, $filtered);
    }

    /** @test */
    public function it_returns_primary_photo(): void
    {
        $asset = Asset::factory()->create();
        $photo1 = File::factory()->photo()->create([
            'fileable_type' => Asset::class,
            'fileable_id' => $asset->id,
            'is_primary' => false,
        ]);
        $photo2 = File::factory()->photo()->primary()->create([
            'fileable_type' => Asset::class,
            'fileable_id' => $asset->id,
        ]);

        $primaryPhoto = $asset->primaryPhoto();

        $this->assertEquals($photo2->id, $primaryPhoto->id);
    }

    /** @test */
    public function it_returns_first_photo_when_no_primary(): void
    {
        $asset = Asset::factory()->create();
        $photo1 = File::factory()->photo()->create([
            'fileable_type' => Asset::class,
            'fileable_id' => $asset->id,
            'is_primary' => false,
            'sort_order' => 1,
        ]);
        $photo2 = File::factory()->photo()->create([
            'fileable_type' => Asset::class,
            'fileable_id' => $asset->id,
            'is_primary' => false,
            'sort_order' => 2,
        ]);

        $primaryPhoto = $asset->primaryPhoto();

        $this->assertNotNull($primaryPhoto);
        $this->assertContains($primaryPhoto->id, [$photo1->id, $photo2->id]);
    }

    /** @test */
    public function it_calculates_lifetime_percentage(): void
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
    public function it_returns_null_lifetime_percentage_without_expected_value(): void
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
    public function it_handles_parent_child_relationships(): void
    {
        $parent = Asset::factory()->create();
        $child = Asset::factory()->create();
        
        // Create component relationship
        \App\Models\AssetComponent::factory()->active()->create([
            'parent_asset_id' => $parent->id,
            'component_asset_id' => $child->id,
        ]);

        $this->assertTrue($parent->hasComponents());
        $this->assertTrue($child->isComponent());
        $this->assertEquals(1, $parent->activeComponents->count());
    }

    /** @test */
    public function scope_components_returns_only_components(): void
    {
        $component = Asset::factory()->create();
        $notComponent = Asset::factory()->create();
        
        \App\Models\AssetComponent::factory()->active()->create([
            'component_asset_id' => $component->id,
        ]);

        $components = Asset::components()->get();

        $this->assertCount(1, $components);
        $this->assertEquals($component->id, $components->first()->id);
    }

    /** @test */
    public function scope_with_components_returns_assets_with_children(): void
    {
        $parent1 = Asset::factory()->create();
        $child = Asset::factory()->create();
        Asset::factory()->create(); // No children
        
        \App\Models\AssetComponent::factory()->active()->create([
            'parent_asset_id' => $parent1->id,
            'component_asset_id' => $child->id,
        ]);

        $withComponents = Asset::withComponents()->get();

        $this->assertCount(1, $withComponents);
        $this->assertEquals($parent1->id, $withComponents->first()->id);
    }

    /** @test */
    public function it_casts_specifications_to_value_object(): void
    {
        $specs = ['voltage' => '220V', 'power' => '1000W'];
        $asset = Asset::factory()->create(['specifications' => $specs]);

        $this->assertInstanceOf(\App\ValueObjects\AssetSpecifications::class, $asset->specifications);
        $this->assertEquals('220V', $asset->specifications->voltage);
        $this->assertEquals('1000W', $asset->specifications->power);
    }

    /** @test */
    public function it_casts_lifetime_unit_to_enum(): void
    {
        $asset = Asset::factory()->create(['lifetime_unit' => 'days']);

        $this->assertInstanceOf(UsageUnit::class, $asset->lifetime_unit);
        $this->assertEquals(UsageUnit::Days, $asset->lifetime_unit);
    }
}
