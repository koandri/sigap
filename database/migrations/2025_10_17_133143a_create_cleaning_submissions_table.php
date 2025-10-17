<?php

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
        Schema::create('cleaning_submissions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cleaning_task_id')->constrained()->onDelete('cascade');
            $table->foreignId('submitted_by')->constrained('users')->onDelete('cascade');
            $table->timestamp('submitted_at');
            $table->json('before_photo'); // Store watermarked photo path and metadata
            $table->json('after_photo'); // Store watermarked photo path and metadata
            $table->text('notes')->nullable();
            $table->timestamps();
            
            $table->index(['cleaning_task_id']);
            $table->index(['submitted_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cleaning_submissions');
    }
};
