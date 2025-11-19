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
        Schema::create('production_plan_step5', function (Blueprint $table) {
            $table->id();
            $table->foreignId('production_plan_id')->constrained('production_plans')->onDelete('cascade');
            $table->foreignId('pack_sku_id')->constrained('items')->onDelete('restrict');
            $table->foreignId('packing_material_item_id')->constrained('items')->onDelete('restrict');
            $table->integer('quantity_total')->default(0);
            $table->timestamps();

            $table->index('production_plan_id');
            $table->index('pack_sku_id');
            $table->index('packing_material_item_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('production_plan_step5');
    }
};
