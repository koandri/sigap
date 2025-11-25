<?php

declare(strict_types=1);

use App\Enums\FileCategory;
use App\Models\File;
use App\Models\WorkOrder;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Migrate work_order_photos to files table
        DB::table('work_order_photos')
            ->orderBy('id')
            ->chunk(100, function ($photos) {
                foreach ($photos as $photo) {
                    File::create([
                        'fileable_type' => WorkOrder::class,
                        'fileable_id' => $photo->work_order_id,
                        'file_category' => FileCategory::Photo,
                        'file_path' => $photo->photo_path,
                        'file_name' => basename($photo->photo_path),
                        'uploaded_at' => $photo->created_at,
                        'uploaded_by' => $photo->uploaded_by,
                        'caption' => $photo->caption,
                        'metadata' => [
                            'photo_type' => $photo->photo_type,
                        ],
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
        // Delete migrated work order photos
        File::where('fileable_type', WorkOrder::class)
            ->where('file_category', FileCategory::Photo)
            ->delete();
    }
};
