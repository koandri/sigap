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
        Schema::create('packing_material_requirements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('production_plan_step4_id')->constrained('production_plan_step4')->onDelete('cascade');
            $table->foreignId('packing_material_item_id')->constrained('items')->onDelete('restrict');
            $table->decimal('quantity_per_unit', 10, 3); // How many of this material per packing unit
            $table->timestamps();

            $table->index('production_plan_step4_id');
            $table->index('packing_material_item_id');
            // Ensure no duplicate materials in same step4
            $table->unique(['production_plan_step4_id', 'packing_material_item_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('packing_material_requirements');
    }
};
