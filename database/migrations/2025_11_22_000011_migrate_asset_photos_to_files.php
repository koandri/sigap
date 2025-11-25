<?php

declare(strict_types=1);

use App\Enums\FileCategory;
use App\Models\Asset;
use App\Models\File;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Migrate asset_photos to files table
        DB::table('asset_photos')
            ->orderBy('id')
            ->chunk(100, function ($photos) {
                foreach ($photos as $photo) {
                    File::create([
                        'fileable_type' => Asset::class,
                        'fileable_id' => $photo->asset_id,
                        'file_category' => FileCategory::Photo,
                        'file_path' => $photo->photo_path,
                        'file_name' => basename($photo->photo_path),
                        'uploaded_at' => $photo->uploaded_at ?? $photo->created_at,
                        'uploaded_by' => $photo->uploaded_by,
                        'metadata' => [
                            'gps_data' => $photo->gps_data ? json_decode($photo->gps_data, true) : null,
                            'captured_at' => $photo->captured_at,
                            'original_metadata' => $photo->metadata ? json_decode($photo->metadata, true) : null,
                        ],
                        'is_primary' => (bool) $photo->is_primary,
                        'created_at' => $photo->created_at,
                        'updated_at' => $photo->updated_at,
                    ]);
                }
            });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Delete migrated asset photos
        File::where('fileable_type', Asset::class)
            ->where('file_category', FileCategory::Photo)
            ->delete();
    }
};
