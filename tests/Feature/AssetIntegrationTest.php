<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Asset;
use App\Models\AssetCategory;
use App\Models\AssetPhoto;
use App\Models\Department;
use App\Models\Location;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

final class AssetIntegrationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('s3');
    }

    /** @test */
    public function user_can_create_asset_with_basic_information(): void
    {
        $user = User::factory()->create();
        $category = AssetCategory::factory()->create();
        $location = Location::factory()->create();

        $this->actingAs($user);

        $response = $this->post(route('options.assets.store'), [
            'name' => 'Test Asset',
            'asset_category_id' => $category->id,
            'location_id' => $location->id,
            'status' => 'operational',
        ]);

        $response->assertRedirect(route('options.assets.index'));
        $this->assertDatabaseHas('assets', [
            'name' => 'Test Asset',
            'asset_category_id' => $category->id,
        ]);
    }

    /** @test */
    public function asset_code_is_auto_generated_if_not_provided(): void
    {
        $user = User::factory()->create();
        $category = AssetCategory::factory()->create(['code' => 'COMP']);

        $this->actingAs($user);

        $this->post(route('options.assets.store'), [
            'name' => 'Test Asset',
            'asset_category_id' => $category->id,
            'status' => 'operational',
        ]);

        $asset = Asset::first();
        $this->assertNotNull($asset->code);
        $this->assertStringContainsString('COMP', $asset->code);
    }

    /** @test */
    public function user_can_upload_multiple_photos_when_creating_asset(): void
    {
        $user = User::factory()->create();
        $category = AssetCategory::factory()->create();

        $this->actingAs($user);

        $photo1 = UploadedFile::fake()->image('photo1.jpg');
        $photo2 = UploadedFile::fake()->image('photo2.jpg');

        $this->post(route('options.assets.store'), [
            'name' => 'Test Asset',
            'asset_category_id' => $category->id,
            'status' => 'operational',
            'photos' => [$photo1, $photo2],
        ]);

        $asset = Asset::first();
        $this->assertCount(2, $asset->photos);
    }

    /** @test */
    public function first_photo_is_set_as_primary(): void
    {
        $user = User::factory()->create();
        $category = AssetCategory::factory()->create();

        $this->actingAs($user);

        $photo1 = UploadedFile::fake()->image('photo1.jpg');
        $photo2 = UploadedFile::fake()->image('photo2.jpg');

        $this->post(route('options.assets.store'), [
            'name' => 'Test Asset',
            'asset_category_id' => $category->id,
            'status' => 'operational',
            'photos' => [$photo1, $photo2],
        ]);

        $asset = Asset::first();
        $primaryPhoto = $asset->primaryPhoto();
        
        $this->assertNotNull($primaryPhoto);
        $this->assertTrue($primaryPhoto->is_primary);
    }

    /** @test */
    public function qr_code_is_generated_when_asset_is_created(): void
    {
        $user = User::factory()->create();
        $category = AssetCategory::factory()->create();

        $this->actingAs($user);

        $this->post(route('options.assets.store'), [
            'name' => 'Test Asset',
            'asset_category_id' => $category->id,
            'status' => 'operational',
        ]);

        $asset = Asset::first();
        $this->assertNotNull($asset->qr_code_path);
        Storage::disk('s3')->assertExists($asset->qr_code_path);
    }

    /** @test */
    public function user_can_attach_component_to_asset(): void
    {
        $user = User::factory()->create();
        $parent = Asset::factory()->create();
        $component = Asset::factory()->create();

        $this->actingAs($user);

        $response = $this->post(route('options.assets.attach-component', $parent), [
            'component_asset_id' => $component->id,
            'component_type' => 'replaceable',
            'installed_date' => now()->format('Y-m-d'),
        ]);

        $response->assertRedirect();
        $component->refresh();
        
        $this->assertEquals($parent->id, $component->parent_asset_id);
        $this->assertEquals('replaceable', $component->component_type->value);
    }

    /** @test */
    public function user_can_detach_component_from_asset(): void
    {
        $user = User::factory()->create();
        $parent = Asset::factory()->create();
        $component = Asset::factory()->create(['parent_asset_id' => $parent->id]);

        $this->actingAs($user);

        $response = $this->post(route('options.assets.detach-component', $component));

        $response->assertRedirect();
        $component->refresh();
        
        $this->assertNull($component->parent_asset_id);
    }

    /** @test */
    public function user_can_set_primary_photo(): void
    {
        $user = User::factory()->create();
        $asset = Asset::factory()->create();
        $photo1 = AssetPhoto::factory()->create(['asset_id' => $asset->id, 'is_primary' => true]);
        $photo2 = AssetPhoto::factory()->create(['asset_id' => $asset->id, 'is_primary' => false]);

        $this->actingAs($user);

        $response = $this->post(route('options.assets.set-primary-photo', [$asset, $photo2]));

        $response->assertJson(['success' => true]);
        
        $photo1->refresh();
        $photo2->refresh();
        
        $this->assertFalse($photo1->is_primary);
        $this->assertTrue($photo2->is_primary);
    }

    /** @test */
    public function user_can_delete_photo(): void
    {
        $user = User::factory()->create();
        $asset = Asset::factory()->create();
        $photo = AssetPhoto::factory()->create(['asset_id' => $asset->id]);

        $this->actingAs($user);

        $response = $this->delete(route('options.assets.delete-photo', $photo));

        $response->assertJson(['success' => true]);
        $this->assertDatabaseMissing('asset_photos', ['id' => $photo->id]);
    }

    /** @test */
    public function cannot_delete_asset_with_child_components(): void
    {
        $user = User::factory()->create();
        $parent = Asset::factory()->create();
        Asset::factory()->create(['parent_asset_id' => $parent->id]);

        $this->actingAs($user);

        $response = $this->delete(route('options.assets.destroy', $parent));

        $response->assertRedirect();
        $response->assertSessionHas('error');
        $this->assertDatabaseHas('assets', ['id' => $parent->id]);
    }

    /** @test */
    public function asset_specifications_are_stored_as_json(): void
    {
        $user = User::factory()->create();
        $category = AssetCategory::factory()->create();

        $this->actingAs($user);

        $specs = [
            'voltage' => '220V',
            'power' => '1000W',
            'weight' => '5kg',
        ];

        $this->post(route('options.assets.store'), [
            'name' => 'Test Asset',
            'asset_category_id' => $category->id,
            'status' => 'operational',
            'specifications' => json_encode($specs),
        ]);

        $asset = Asset::first();
        $this->assertEquals('220V', $asset->specifications['voltage']);
        $this->assertEquals('1000W', $asset->specifications['power']);
    }

    /** @test */
    public function user_can_view_asset_details(): void
    {
        $user = User::factory()->create();
        $asset = Asset::factory()->create();

        $this->actingAs($user);

        $response = $this->get(route('options.assets.show', $asset));

        $response->assertOk();
        $response->assertSee($asset->name);
        $response->assertSee($asset->code);
    }

    /** @test */
    public function user_can_filter_assets_by_category(): void
    {
        $user = User::factory()->create();
        $category1 = AssetCategory::factory()->create();
        $category2 = AssetCategory::factory()->create();
        
        Asset::factory()->count(2)->create(['asset_category_id' => $category1->id]);
        Asset::factory()->create(['asset_category_id' => $category2->id]);

        $this->actingAs($user);

        $response = $this->get(route('options.assets.index', ['category' => $category1->id]));

        $response->assertOk();
        // Should see 2 assets from category1
    }

    /** @test */
    public function user_can_search_assets_by_name_or_code(): void
    {
        $user = User::factory()->create();
        Asset::factory()->create(['name' => 'Laptop Dell', 'code' => 'COMP-001']);
        Asset::factory()->create(['name' => 'Desktop HP', 'code' => 'COMP-002']);

        $this->actingAs($user);

        $response = $this->get(route('options.assets.index', ['search' => 'Laptop']));

        $response->assertOk();
        $response->assertSee('Laptop Dell');
        $response->assertDontSee('Desktop HP');
    }
}
