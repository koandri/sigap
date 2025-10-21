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
        Schema::create('document_instances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('template_document_version_id')->constrained('document_versions')->onDelete('cascade');
            $table->string('instance_number')->unique();
            $table->string('subject');
            $table->text('content_summary')->nullable();
            $table->foreignId('created_by')->constrained('users');
            $table->string('status');
            $table->string('final_pdf_path')->nullable();
            $table->foreignId('approved_by')->nullable()->constrained('users');
            $table->timestamp('approved_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('document_instances');
    }
};
