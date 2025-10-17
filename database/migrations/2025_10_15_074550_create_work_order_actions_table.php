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
        Schema::create('work_order_actions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('work_order_id')->constrained()->onDelete('cascade');
            $table->foreignId('performed_by')->constrained('users')->onDelete('cascade');
            $table->enum('action_type', [
                'spare-part-replacement',
                'send-for-repair',
                'retire-equipment',
                'cleaning',
                'adjustment',
                'calibration',
                'enhancement',
                'other'
            ]);
            $table->text('action_description');
            $table->text('notes')->nullable();
            $table->timestamp('performed_at');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('work_order_actions');
    }
};