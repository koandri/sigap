<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('yield_guidelines')) {
            return;
        }

        if (Schema::hasColumn('yield_guidelines', 'is_active')) {
            Schema::table('yield_guidelines', function (Blueprint $table): void {
                $table->dropColumn('is_active');
            });
        }

        if (Schema::hasColumn('yield_guidelines', 'unit')) {
            Schema::table('yield_guidelines', function (Blueprint $table): void {
                $table->dropColumn('unit');
            });
        }
    }

    public function down(): void
    {
        if (!Schema::hasTable('yield_guidelines')) {
            return;
        }

        Schema::table('yield_guidelines', function (Blueprint $table): void {
            if (!Schema::hasColumn('yield_guidelines', 'unit')) {
                $table->string('unit', 15)->after('yield_quantity');
            }

            if (!Schema::hasColumn('yield_guidelines', 'is_active')) {
                $table->boolean('is_active')->default(true)->after('unit');
            }
        });

        if (Schema::hasColumn('yield_guidelines', 'is_active')) {
            Schema::table('yield_guidelines', function (Blueprint $table): void {
                $table->index(
                    ['from_item_id', 'is_active'],
                    'yield_guidelines_from_item_id_is_active_index'
                );
                $table->index(
                    ['to_item_id', 'is_active'],
                    'yield_guidelines_to_item_id_is_active_index'
                );
                $table->index(
                    ['from_stage', 'to_stage', 'is_active'],
                    'yield_guidelines_from_stage_to_stage_is_active_index'
                );
            });
        }
    }
};



