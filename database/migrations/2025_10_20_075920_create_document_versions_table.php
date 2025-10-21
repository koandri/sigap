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
        Schema::create('document_versions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('document_id')->constrained()->onDelete('cascade');
            $table->unsignedInteger('version_number');
            $table->string('file_path');
            $table->string('file_type'); // docx, xlsx, pdf, jpg
            $table->string('status');
            $table->foreignId('created_by')->constrained('users');
            $table->text('revision_description')->nullable();
            $table->timestamp('finalized_at')->nullable();
            $table->timestamps();
            
            $table->unique(['document_id', 'version_number']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('document_versions');
    }
};
