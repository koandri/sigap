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
        Schema::create('production_sites', function (Blueprint $table) {
            $table->id();
            $table->string('code', 10)->unique(); // GL1, GL2, TA, BL
            $table->string('name', 100); // Gelam 1, Gelam 2, Tanggulangin, Bulang
            $table->text('description')->nullable();
            $table->boolean('is_main_site')->default(false); // Main site for dough production (Bulang)
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0);
            $table->timestamps();

            $table->index('is_active');
            $table->index('is_main_site');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('production_sites');
    }
};
