<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('form_versions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('form_id')->constrained()->onDelete('cascade');
            $table->integer('version_number')->default(0); // Starts from 0
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(false);
            $table->json('metadata')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamp('created_on')->useCurrent(); // Added created_on
            $table->timestamps();
            
            $table->unique(['form_id', 'version_number']);
            $table->index(['form_id', 'is_active']);
            $table->foreign('created_by')->references('id')->on('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('form_versions');
    }
};