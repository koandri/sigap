<?php

declare(strict_types=1);

namespace Tests\Unit\Models\Concerns;

use App\Enums\FileCategory;
use App\Models\Asset;
use App\Models\File;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

final class HasFilesTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('s3');
    }

    /** @test */
    public function it_has_files_relationship(): void
    {
        $asset = Asset::factory()->create();
        File::factory()->count(3)->create([
            'fileable_type' => Asset::class,
            'fileable_id' => $asset->id,
        ]);

        $this->assertCount(3, $asset->files);
    }

    /** @test */
    public function it_has_photos_relationship(): void
    {
        $asset = Asset::factory()->create();
        File::factory()->photo()->count(2)->create([
            'fileable_type' => Asset::class,
            'fileable_id' => $asset->id,
        ]);
        File::factory()->document()->create([
            'fileable_type' => Asset::class,
            'fileable_id' => $asset->id,
        ]);

        $this->assertCount(2, $asset->photos);
    }

    /** @test */
    public function it_has_documents_relationship(): void
    {
        $asset = Asset::factory()->create();
        File::factory()->photo()->create([
            'fileable_type' => Asset::class,
            'fileable_id' => $asset->id,
        ]);
        File::factory()->document()->count(2)->create([
            'fileable_type' => Asset::class,
            'fileable_id' => $asset->id,
        ]);

        $this->assertCount(2, $asset->documents);
    }

    /** @test */
    public function it_gets_primary_photo(): void
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

        $primary = $asset->primaryPhoto();

        $this->assertEquals($photo2->id, $primary->id);
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
            'created_at' => now()->subMinutes(2),
        ]);
        $photo2 = File::factory()->photo()->create([
            'fileable_type' => Asset::class,
            'fileable_id' => $asset->id,
            'is_primary' => false,
            'sort_order' => 2,
            'created_at' => now()->subMinutes(1),
        ]);

        $primary = $asset->primaryPhoto();

        // Should return first by sort_order
        $this->assertNotNull($primary);
        $this->assertContains($primary->id, [$photo1->id, $photo2->id]);
    }

    /** @test */
    public function it_can_add_file(): void
    {
        $asset = Asset::factory()->create();
        $uploadedFile = UploadedFile::fake()->image('photo.jpg');

        $file = $asset->addFile(
            $uploadedFile,
            FileCategory::Photo,
            'Test caption'
        );

        $this->assertInstanceOf(File::class, $file);
        $this->assertEquals(FileCategory::Photo, $file->file_category);
        $this->assertEquals('Test caption', $file->caption);
        $this->assertEquals('photo.jpg', $file->file_name);
        Storage::disk('s3')->assertExists($file->file_path);
    }

    /** @test */
    public function it_can_set_primary_photo(): void
    {
        $asset = Asset::factory()->create();
        $photo1 = File::factory()->photo()->primary()->create([
            'fileable_type' => Asset::class,
            'fileable_id' => $asset->id,
        ]);
        $photo2 = File::factory()->photo()->create([
            'fileable_type' => Asset::class,
            'fileable_id' => $asset->id,
            'is_primary' => false,
        ]);

        $asset->setPrimaryPhoto($photo2);

        $this->assertFalse($photo1->fresh()->is_primary);
        $this->assertTrue($photo2->fresh()->is_primary);
    }

    /** @test */
    public function it_checks_if_has_files(): void
    {
        $assetWithFiles = Asset::factory()->create();
        File::factory()->create([
            'fileable_type' => Asset::class,
            'fileable_id' => $assetWithFiles->id,
        ]);

        $assetWithoutFiles = Asset::factory()->create();

        $this->assertTrue($assetWithFiles->hasFiles());
        $this->assertFalse($assetWithoutFiles->hasFiles());
    }

    /** @test */
    public function it_checks_if_has_photos(): void
    {
        $asset = Asset::factory()->create();
        File::factory()->photo()->create([
            'fileable_type' => Asset::class,
            'fileable_id' => $asset->id,
        ]);

        $this->assertTrue($asset->hasPhotos());
    }

    /** @test */
    public function it_checks_if_has_documents(): void
    {
        $asset = Asset::factory()->create();
        File::factory()->document()->create([
            'fileable_type' => Asset::class,
            'fileable_id' => $asset->id,
        ]);

        $this->assertTrue($asset->hasDocuments());
    }

    /** @test */
    public function it_calculates_total_file_size(): void
    {
        $asset = Asset::factory()->create();
        File::factory()->create([
            'fileable_type' => Asset::class,
            'fileable_id' => $asset->id,
            'file_size' => 1024,
        ]);
        File::factory()->create([
            'fileable_type' => Asset::class,
            'fileable_id' => $asset->id,
            'file_size' => 2048,
        ]);

        $totalSize = $asset->getTotalFileSize();

        $this->assertEquals(3072, $totalSize);
    }

    /** @test */
    public function it_formats_total_file_size(): void
    {
        $asset = Asset::factory()->create();
        File::factory()->create([
            'fileable_type' => Asset::class,
            'fileable_id' => $asset->id,
            'file_size' => 1024 * 1024, // 1 MB
        ]);

        $formattedSize = $asset->getFormattedTotalFileSize();

        $this->assertEquals('1 MB', $formattedSize);
    }
}
