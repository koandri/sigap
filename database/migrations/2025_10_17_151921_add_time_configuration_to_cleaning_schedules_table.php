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
        Schema::table('cleaning_schedules', function (Blueprint $table) {
            // Add time configuration for scheduling
            $table->time('scheduled_time')->nullable()->after('frequency_config')
                  ->comment('Specific time for daily/weekly/monthly tasks');
            
            $table->time('start_time')->nullable()->after('scheduled_time')
                  ->comment('Start time for hourly frequency range');
            
            $table->time('end_time')->nullable()->after('start_time')
                  ->comment('End time for hourly frequency range');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('cleaning_schedules', function (Blueprint $table) {
            $table->dropColumn(['scheduled_time', 'start_time', 'end_time']);
        });
    }
};
