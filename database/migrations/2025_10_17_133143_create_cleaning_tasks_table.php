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
        Schema::create('cleaning_tasks', function (Blueprint $table) {
            $table->id();
            $table->string('task_number')->unique();
            $table->foreignId('cleaning_schedule_id')->constrained()->onDelete('cascade');
            $table->foreignId('cleaning_schedule_item_id')->constrained()->onDelete('cascade');
            $table->foreignId('location_id')->constrained()->onDelete('cascade');
            $table->foreignId('asset_id')->nullable()->constrained()->onDelete('set null');
            $table->string('item_name');
            $table->text('item_description')->nullable();
            $table->date('scheduled_date');
            $table->foreignId('assigned_to')->constrained('users')->onDelete('cascade');
            $table->foreignId('started_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('started_at')->nullable();
            $table->string('status')->default('pending'); // pending, in-progress, completed, missed, approved, rejected
            $table->timestamp('completed_at')->nullable();
            $table->foreignId('completed_by')->nullable()->constrained('users')->onDelete('set null');
            $table->text('skip_reason')->nullable();
            $table->timestamps();
            
            $table->index(['assigned_to', 'scheduled_date', 'status']);
            $table->index(['location_id', 'scheduled_date', 'status']);
            $table->index(['scheduled_date', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cleaning_tasks');
    }
};
