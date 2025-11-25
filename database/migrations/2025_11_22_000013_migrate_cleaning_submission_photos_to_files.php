<?php

declare(strict_types=1);

use App\Enums\FileCategory;
use App\Models\CleaningSubmission;
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
        // Migrate cleaning_submission photos to files table
        DB::table('cleaning_submissions')
            ->orderBy('id')
            ->chunk(100, function ($submissions) {
                foreach ($submissions as $submission) {
                    // Before photo
                    if ($submission->before_photo) {
                        $beforeData = json_decode($submission->before_photo, true);
                        
                        // Handle both array format and simple string
                        $beforePath = is_array($beforeData) 
                            ? ($beforeData['path'] ?? $beforeData[0] ?? null)
                            : $beforeData;
                        
                        if ($beforePath) {
                            File::create([
                                'fileable_type' => CleaningSubmission::class,
                                'fileable_id' => $submission->id,
                                'file_category' => FileCategory::Photo,
                                'file_path' => $beforePath,
                                'file_name' => basename($beforePath),
                                'uploaded_at' => $submission->submitted_at ?? $submission->created_at,
                                'uploaded_by' => $submission->submitted_by,
                                'caption' => 'Before',
                                'metadata' => ['type' => 'before'],
                                'sort_order' => 1,
                                'created_at' => $submission->created_at,
                                'updated_at' => $submission->updated_at,
                            ]);
                        }
                    }
                    
                    // After photo
                    if ($submission->after_photo) {
                        $afterData = json_decode($submission->after_photo, true);
                        
                        // Handle both array format and simple string
                        $afterPath = is_array($afterData)
                            ? ($afterData['path'] ?? $afterData[0] ?? null)
                            : $afterData;
                        
                        if ($afterPath) {
                            File::create([
                                'fileable_type' => CleaningSubmission::class,
                                'fileable_id' => $submission->id,
                                'file_category' => FileCategory::Photo,
                                'file_path' => $afterPath,
                                'file_name' => basename($afterPath),
                                'uploaded_at' => $submission->submitted_at ?? $submission->created_at,
                                'uploaded_by' => $submission->submitted_by,
                                'caption' => 'After',
                                'metadata' => ['type' => 'after'],
                                'sort_order' => 2,
                                'created_at' => $submission->created_at,
                                'updated_at' => $submission->updated_at,
                            ]);
                        }
                    }
                }
            });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Delete migrated cleaning submission photos
        File::where('fileable_type', CleaningSubmission::class)
            ->where('file_category', FileCategory::Photo)
            ->delete();
    }
};
