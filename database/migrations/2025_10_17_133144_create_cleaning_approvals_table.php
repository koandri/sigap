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
        Schema::create('cleaning_approvals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cleaning_submission_id')->constrained()->onDelete('cascade');
            $table->boolean('is_flagged_for_review')->default(false);
            $table->timestamp('reviewed_at')->nullable();
            $table->foreignId('approved_by')->nullable()->constrained('users')->onDelete('set null');
            $table->string('status')->default('pending'); // pending, approved, rejected
            $table->text('notes')->nullable();
            $table->timestamps();
            
            $table->index(['status', 'is_flagged_for_review']);
            $table->index(['cleaning_submission_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cleaning_approvals');
    }
};
