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
        Schema::create('production_plan_step3', function (Blueprint $table) {
            $table->id();
            $table->foreignId('production_plan_id')->constrained('production_plans')->onDelete('cascade');
            $table->foreignId('gelondongan_item_id')->constrained('items')->onDelete('restrict');
            $table->foreignId('kerupuk_kering_item_id')->constrained('items')->onDelete('restrict');
            $table->integer('qty_gl1_gelondongan')->default(0);
            $table->decimal('qty_gl1_kg', 10, 2)->default(0);
            $table->integer('qty_gl2_gelondongan')->default(0);
            $table->decimal('qty_gl2_kg', 10, 2)->default(0);
            $table->integer('qty_ta_gelondongan')->default(0);
            $table->decimal('qty_ta_kg', 10, 2)->default(0);
            $table->integer('qty_bl_gelondongan')->default(0);
            $table->decimal('qty_bl_kg', 10, 2)->default(0);
            $table->timestamps();

            $table->index('production_plan_id');
            $table->index('gelondongan_item_id');
            $table->index('kerupuk_kering_item_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('production_plan_step3');
    }
};
