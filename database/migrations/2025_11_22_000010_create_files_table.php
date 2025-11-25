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
        Schema::create('files', function (Blueprint $table) {
            $table->id();
            $table->morphs('fileable'); // fileable_type, fileable_id (creates index automatically)
            $table->string('file_category'); // FileCategory enum
            $table->string('file_path');
            $table->string('file_name');
            $table->bigInteger('file_size')->nullable(); // bytes
            $table->string('mime_type')->nullable();
            $table->timestamp('uploaded_at');
            $table->foreignId('uploaded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->json('metadata')->nullable(); // gps_data, dimensions, etc.
            $table->boolean('is_primary')->default(false);
            $table->text('caption')->nullable();
            $table->integer('sort_order')->default(0);
            $table->timestamps();
            
            // Additional indexes (morphs already creates fileable index)
            $table->index('file_category');
            $table->index('is_primary');
            $table->index('uploaded_at');
            $table->index('sort_order');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('files');
    }
};
