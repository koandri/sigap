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
        Schema::create('work_orders', function (Blueprint $table) {
            $table->id();
            $table->string('wo_number')->unique();
            $table->foreignId('asset_id')->nullable()->constrained()->onDelete('cascade');
            $table->foreignId('maintenance_type_id')->nullable()->constrained()->onDelete('set null');
            $table->enum('priority', ['low', 'medium', 'high', 'critical'])->default('medium');
            $table->enum('status', ['pending', 'in-progress', 'completed', 'cancelled'])->default('pending');
            $table->datetime('scheduled_date')->nullable();
            $table->datetime('completed_date')->nullable();
            $table->foreignId('assigned_to')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('requested_by')->constrained('users')->onDelete('cascade');
            $table->decimal('estimated_hours', 8, 2)->nullable();
            $table->decimal('actual_hours', 8, 2)->nullable();
            $table->text('description')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            
            $table->index(['status', 'scheduled_date']);
            $table->index(['assigned_to', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('work_orders');
    }
};

