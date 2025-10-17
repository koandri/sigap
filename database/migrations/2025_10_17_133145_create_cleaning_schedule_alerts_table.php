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
        Schema::create('cleaning_schedule_alerts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cleaning_schedule_id')->constrained()->onDelete('cascade');
            $table->foreignId('cleaning_schedule_item_id')->constrained()->onDelete('cascade');
            $table->foreignId('asset_id')->nullable()->constrained()->onDelete('set null');
            $table->string('alert_type'); // asset_inactive, asset_disposed
            $table->timestamp('detected_at');
            $table->timestamp('resolved_at')->nullable();
            $table->foreignId('resolved_by')->nullable()->constrained('users')->onDelete('set null');
            $table->text('resolution_notes')->nullable();
            $table->timestamps();
            
            $table->index(['cleaning_schedule_id', 'resolved_at']);
            $table->index(['alert_type', 'resolved_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cleaning_schedule_alerts');
    }
};
