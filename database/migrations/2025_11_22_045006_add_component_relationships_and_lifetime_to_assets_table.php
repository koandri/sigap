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
            $table->foreignId('parent_asset_id')->nullable()->after('id')->constrained('assets')->onDelete('restrict');
            $table->string('component_type')->nullable()->after('parent_asset_id');
            $table->date('installed_date')->nullable()->after('component_type');
            $table->decimal('installed_usage_value', 15, 2)->nullable()->after('installed_date');
            $table->decimal('disposed_usage_value', 15, 2)->nullable()->after('installed_usage_value');
            $table->string('lifetime_unit')->nullable()->after('disposed_usage_value');
            $table->decimal('expected_lifetime_value', 15, 2)->nullable()->after('lifetime_unit');
            $table->decimal('actual_lifetime_value', 15, 2)->nullable()->after('expected_lifetime_value');
            $table->text('installation_notes')->nullable()->after('actual_lifetime_value');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('assets', function (Blueprint $table) {
            $table->dropForeign(['parent_asset_id']);
            $table->dropColumn([
                'parent_asset_id',
                'component_type',
                'installed_date',
                'installed_usage_value',
                'disposed_usage_value',
                'lifetime_unit',
                'expected_lifetime_value',
                'actual_lifetime_value',
                'installation_notes',
            ]);
        });
    }
};
