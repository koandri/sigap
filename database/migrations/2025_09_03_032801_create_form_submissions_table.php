<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('form_submissions', function (Blueprint $table) {
            $table->id();
            $table->string('submission_code', 50)->unique(); // Explained below
            $table->foreignId('form_version_id')->constrained();
            $table->unsignedBigInteger('submitted_by');
            $table->enum('status', [
                'draft',
                'submitted',
                'under_review',
                'approved',
                'rejected',
                'cancelled'
            ])->default('draft');
            $table->json('metadata')->nullable();
            $table->timestamp('submitted_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
            
            $table->index('submission_code');
            $table->index('status');
            $table->index(['submitted_by', 'status']);
            
            $table->foreign('submitted_by')->references('id')->on('users');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('form_submissions');
    }
};