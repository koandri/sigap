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
        Schema::table('assets', function (Blueprint $table) {
            // Check if columns exist before dropping (for test database compatibility)
            if (Schema::hasColumn('assets', 'parent_asset_id')) {
                $table->dropForeign(['parent_asset_id']);
                $table->dropColumn('parent_asset_id');
            }
            
            if (Schema::hasColumn('assets', 'component_type')) {
                $table->dropColumn('component_type');
            }
            
            if (Schema::hasColumn('assets', 'installation_notes')) {
                $table->dropColumn('installation_notes');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('assets', function (Blueprint $table) {
            // Restore columns
            $table->foreignId('parent_asset_id')->nullable()->after('id')->constrained('assets')->onDelete('restrict');
            $table->string('component_type')->nullable()->after('parent_asset_id');
            $table->text('installation_notes')->nullable();
        });
    }
};
