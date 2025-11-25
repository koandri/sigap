<?php

declare(strict_types=1);

namespace Tests\Unit\Models;

use App\Enums\FileCategory;
use App\Models\Asset;
use App\Models\File;
use App\Models\User;
use App\ValueObjects\GpsData;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class FileTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_belongs_to_fileable_model(): void
    {
        $asset = Asset::factory()->create();
        $file = File::factory()->create([
            'fileable_type' => Asset::class,
            'fileable_id' => $asset->id,
        ]);

        $this->assertInstanceOf(Asset::class, $file->fileable);
        $this->assertEquals($asset->id, $file->fileable->id);
    }

    /** @test */
    public function it_belongs_to_uploaded_by_user(): void
    {
        $user = User::factory()->create();
        $file = File::factory()->create(['uploaded_by' => $user->id]);

        $this->assertInstanceOf(User::class, $file->uploadedBy);
        $this->assertEquals($user->id, $file->uploadedBy->id);
    }

    /** @test */
    public function scope_photos_returns_only_photos(): void
    {
        File::factory()->photo()->create();
        File::factory()->document()->create();

        $photos = File::photos()->get();

        $this->assertCount(1, $photos);
        $this->assertEquals(FileCategory::Photo, $photos->first()->file_category);
    }

    /** @test */
    public function scope_documents_returns_only_documents(): void
    {
        File::factory()->photo()->create();
        File::factory()->document()->create();

        $documents = File::documents()->get();

        $this->assertCount(1, $documents);
        $this->assertEquals(FileCategory::Document, $documents->first()->file_category);
    }

    /** @test */
    public function scope_primary_returns_only_primary_files(): void
    {
        File::factory()->create(['is_primary' => false]);
        File::factory()->primary()->create();

        $primary = File::primary()->get();

        $this->assertCount(1, $primary);
        $this->assertTrue($primary->first()->is_primary);
    }

    /** @test */
    public function it_checks_if_file_is_image(): void
    {
        $image = File::factory()->create(['mime_type' => 'image/jpeg']);
        $pdf = File::factory()->create(['mime_type' => 'application/pdf']);

        $this->assertTrue($image->isImage());
        $this->assertFalse($pdf->isImage());
    }

    /** @test */
    public function it_checks_if_file_is_video(): void
    {
        $video = File::factory()->create(['mime_type' => 'video/mp4']);
        $image = File::factory()->create(['mime_type' => 'image/jpeg']);

        $this->assertTrue($video->isVideo());
        $this->assertFalse($image->isVideo());
    }

    /** @test */
    public function it_checks_if_file_is_pdf(): void
    {
        $pdf = File::factory()->create(['mime_type' => 'application/pdf']);
        $image = File::factory()->create(['mime_type' => 'image/jpeg']);

        $this->assertTrue($pdf->isPdf());
        $this->assertFalse($image->isPdf());
    }

    /** @test */
    public function it_formats_file_size(): void
    {
        $file = File::factory()->create(['file_size' => 1024]);

        $this->assertEquals('1 KB', $file->getFormattedSize());
    }

    /** @test */
    public function it_gets_file_extension(): void
    {
        $file = File::factory()->create(['file_name' => 'document.pdf']);

        $this->assertEquals('pdf', $file->getExtension());
    }

    /** @test */
    public function it_handles_gps_data(): void
    {
        $gpsData = GpsData::fromArray([
            'latitude' => -6.2088,
            'longitude' => 106.8456,
        ]);

        $file = File::factory()->create();
        $file->setGpsData($gpsData);

        $retrieved = $file->getGpsData();

        $this->assertInstanceOf(GpsData::class, $retrieved);
        $this->assertEquals(-6.2088, $retrieved->latitude);
        $this->assertEquals(106.8456, $retrieved->longitude);
    }

    /** @test */
    public function it_casts_file_category_to_enum(): void
    {
        $file = File::factory()->create(['file_category' => FileCategory::Photo]);

        $this->assertInstanceOf(FileCategory::class, $file->file_category);
        $this->assertEquals(FileCategory::Photo, $file->file_category);
    }
}
