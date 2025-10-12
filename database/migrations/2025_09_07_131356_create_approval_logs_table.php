<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('approval_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('form_submission_id')->constrained()->onDelete('cascade');
            $table->foreignId('approval_flow_step_id')->constrained()->onDelete('cascade');
            $table->unsignedBigInteger('assigned_to')->nullable(); // Who should approve
            $table->unsignedBigInteger('approved_by')->nullable(); // Who actually approved
            $table->enum('status', ['pending', 'approved', 'rejected', 'skipped', 'escalated'])->default('pending');
            $table->text('comments')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamp('assigned_at')->nullable();
            $table->timestamp('action_at')->nullable();
            $table->timestamp('due_at')->nullable(); // SLA deadline
            $table->timestamps();
            
            $table->index(['form_submission_id', 'status']);
            $table->index(['assigned_to', 'status']);
            $table->index('due_at');
            $table->foreign('assigned_to')->references('id')->on('users')->nullOnDelete();
            $table->foreign('approved_by')->references('id')->on('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('approval_logs');
    }
};