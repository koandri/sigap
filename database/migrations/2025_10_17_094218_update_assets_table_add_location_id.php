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
        // Add location_id column
        Schema::table('assets', function (Blueprint $table) {
            $table->foreignId('location_id')->nullable()->after('asset_category_id')->constrained()->onDelete('set null');
        });

        // Migrate existing data: assign random locations to existing assets
        $locationIds = DB::table('locations')->pluck('id')->toArray();
        
        if (!empty($locationIds)) {
            $assets = DB::table('assets')->get();
            
            foreach ($assets as $asset) {
                // Assign a random location from the available locations
                $randomLocationId = $locationIds[array_rand($locationIds)];
                
                DB::table('assets')
                    ->where('id', $asset->id)
                    ->update(['location_id' => $randomLocationId]);
            }
        }

        // Remove old location column
        Schema::table('assets', function (Blueprint $table) {
            $table->dropColumn('location');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Add back location column
        Schema::table('assets', function (Blueprint $table) {
            $table->string('location')->nullable()->after('asset_category_id');
        });

        // Remove location_id column
        Schema::table('assets', function (Blueprint $table) {
            $table->dropForeign(['location_id']);
            $table->dropColumn('location_id');
        });
    }
};
