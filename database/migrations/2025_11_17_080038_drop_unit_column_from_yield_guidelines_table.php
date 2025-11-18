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
        if (!Schema::hasTable('yield_guidelines') || !Schema::hasColumn('yield_guidelines', 'unit')) {
            return;
        }

        Schema::table('yield_guidelines', function (Blueprint $table): void {
            $table->dropColumn('unit');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (!Schema::hasTable('yield_guidelines') || Schema::hasColumn('yield_guidelines', 'unit')) {
            return;
        }

        Schema::table('yield_guidelines', function (Blueprint $table): void {
            $table->string('unit', 15)->after('yield_quantity');
        });
    }
};
