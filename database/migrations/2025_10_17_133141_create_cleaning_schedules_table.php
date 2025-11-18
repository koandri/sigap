<?php

declare(strict_types=1);

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
        Schema::create('cleaning_schedules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('location_id')->constrained()->onDelete('cascade');
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('frequency_type'); // daily, weekly, monthly
            $table->json('frequency_config')->nullable();
            $table->time('scheduled_time')->nullable()
                  ->comment('Specific time for daily/weekly/monthly tasks');
            $table->time('start_time')->nullable()
                  ->comment('Start time for hourly frequency range');
            $table->time('end_time')->nullable()
                  ->comment('End time for hourly frequency range');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            
            $table->index(['location_id', 'is_active']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cleaning_schedules');
    }
};
