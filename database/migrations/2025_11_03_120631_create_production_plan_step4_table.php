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
        Schema::create('production_plan_step4', function (Blueprint $table) {
            $table->id();
            $table->foreignId('production_plan_id')->constrained('production_plans')->onDelete('cascade');
            $table->foreignId('kerupuk_kering_item_id')->constrained('items')->onDelete('restrict');
            $table->foreignId('kerupuk_packing_item_id')->constrained('items')->onDelete('restrict');
            $table->decimal('weight_per_unit', 10, 3); // Kg per packing unit
            $table->decimal('qty_gl1_kg', 10, 3)->default(0);
            $table->decimal('qty_gl1_packing', 10, 3)->default(0);
            $table->decimal('qty_gl2_kg', 10, 3)->default(0);
            $table->decimal('qty_gl2_packing', 10, 3)->default(0);
            $table->decimal('qty_ta_kg', 10, 3)->default(0);
            $table->decimal('qty_ta_packing', 10, 3)->default(0);
            $table->decimal('qty_bl_kg', 10, 3)->default(0);
            $table->decimal('qty_bl_packing', 10, 3)->default(0);
            $table->timestamps();

            $table->index('production_plan_id');
            $table->index('kerupuk_kering_item_id');
            $table->index('kerupuk_packing_item_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('production_plan_step4');
    }
};
