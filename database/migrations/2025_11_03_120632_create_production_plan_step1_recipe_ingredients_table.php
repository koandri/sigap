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
        Schema::create('production_plan_step1_recipe_ingredients', function (Blueprint $table) {
            $table->id();
            $table->foreignId('production_plan_step1_id')
                ->constrained('production_plan_step1')
                ->onDelete('cascade')
                ->name('pp_step1_recipe_ingredients_pp_step1_id_fk');
            $table->foreignId('ingredient_item_id')
                ->constrained('items')
                ->onDelete('restrict')
                ->name('pp_step1_recipe_ingredients_item_id_fk');
            $table->decimal('quantity', 10, 3);
            $table->string('unit', 15)->nullable();
            $table->integer('sort_order')->default(0);
            $table->timestamps();

            $table->index(['production_plan_step1_id', 'sort_order'], 'pp_step1_recipe_ingredients_pp_step1_sort_idx');
            $table->index('ingredient_item_id', 'pp_step1_recipe_ingredients_item_id_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('production_plan_step1_recipe_ingredients');
    }
};
