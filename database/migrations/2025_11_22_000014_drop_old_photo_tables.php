<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Drop old photo tables
        Schema::dropIfExists('asset_photos');
        Schema::dropIfExists('work_order_photos');
        
        // Remove photo columns from cleaning_submissions
        if (Schema::hasTable('cleaning_submissions')) {
            Schema::table('cleaning_submissions', function (Blueprint $table) {
                if (Schema::hasColumn('cleaning_submissions', 'before_photo')) {
                    $table->dropColumn('before_photo');
                }
                if (Schema::hasColumn('cleaning_submissions', 'after_photo')) {
                    $table->dropColumn('after_photo');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Recreate asset_photos table
        Schema::create('asset_photos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('asset_id')->constrained()->onDelete('cascade');
            $table->string('photo_path');
            $table->timestamp('uploaded_at')->nullable();
            $table->timestamp('captured_at')->nullable();
            $table->boolean('is_primary')->default(false);
            $table->foreignId('uploaded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->json('gps_data')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();
        });
        
        // Recreate work_order_photos table
        Schema::create('work_order_photos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('work_order_id')->constrained()->onDelete('cascade');
            $table->foreignId('uploaded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('photo_path');
            $table->string('photo_type')->nullable();
            $table->text('caption')->nullable();
            $table->timestamps();
        });
        
        // Restore photo columns to cleaning_submissions
        if (Schema::hasTable('cleaning_submissions')) {
            Schema::table('cleaning_submissions', function (Blueprint $table) {
                $table->json('before_photo')->nullable();
                $table->json('after_photo')->nullable();
            });
        }
    }
};
