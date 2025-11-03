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
        Schema::create('recipe_ingredients', function (Blueprint $table) {
            $table->id();
            $table->foreignId('recipe_id')->constrained()->onDelete('cascade');
            $table->foreignId('ingredient_item_id')->constrained('items')->onDelete('restrict');
            $table->decimal('quantity', 10, 3); // Quantity of ingredient needed
            $table->string('unit', 15)->nullable(); // Unit of the ingredient
            $table->integer('sort_order')->default(0); // Order in the recipe
            $table->timestamps();

            $table->index(['recipe_id', 'sort_order']);
            $table->index('ingredient_item_id');
            // Ensure no duplicate ingredients in same recipe
            $table->unique(['recipe_id', 'ingredient_item_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('recipe_ingredients');
    }
};
