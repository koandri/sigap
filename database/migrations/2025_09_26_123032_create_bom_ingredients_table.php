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
        Schema::create('bom_ingredients', function (Blueprint $table) {
            $table->id();
            $table->foreignId('bom_template_id')->constrained()->onDelete('cascade');
            $table->foreignId('ingredient_item_id')->constrained('items')->onDelete('restrict'); // The ingredient item
            $table->decimal('quantity', 10, 3); // How much of this ingredient is needed
            $table->string('unit', 15)->nullable(); // Unit of the ingredient
            $table->integer('sort_order')->default(0); // Order in the recipe
            $table->timestamps();

            $table->index(['bom_template_id', 'sort_order']);
            $table->index('ingredient_item_id');
            
            // Ensure no duplicate ingredients in same BoM
            $table->unique(['bom_template_id', 'ingredient_item_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bom_ingredients');
    }
};
