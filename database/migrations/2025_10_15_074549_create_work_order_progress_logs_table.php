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
        Schema::create('work_order_progress_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('work_order_id')->constrained()->onDelete('cascade');
            $table->foreignId('logged_by')->constrained('users')->onDelete('cascade');
            $table->timestamp('logged_at');
            $table->decimal('hours_worked', 8, 2);
            $table->text('progress_notes');
            $table->integer('completion_percentage')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('work_order_progress_logs');
    }
};