<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('maintenance_schedules', function (Blueprint $table) {
            // Add new frequency type field (default to 'daily' for backward compatibility)
            $table->string('frequency_type', 20)->default('daily')->after('maintenance_type_id');
            
            // Add JSON field for frequency configuration
            $table->json('frequency_config')->nullable()->after('frequency_type');
            
            // Make frequency_days nullable since other frequency types might not use it
            $table->integer('frequency_days')->nullable()->change();
        });

        // Migrate existing data: set frequency_type to 'daily' and populate frequency_config
        DB::table('maintenance_schedules')->update([
            'frequency_type' => 'daily',
            'frequency_config' => DB::raw("JSON_OBJECT('interval', frequency_days)")
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('maintenance_schedules', function (Blueprint $table) {
            $table->dropColumn(['frequency_type', 'frequency_config']);
            $table->integer('frequency_days')->nullable(false)->change();
        });
    }
};
