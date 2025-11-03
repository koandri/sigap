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
        // Drop tables in reverse order of dependencies
        Schema::dropIfExists('bom_ingredients');
        Schema::dropIfExists('bom_templates');
        Schema::dropIfExists('bom_types');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Recreate bom_types table
        Schema::create('bom_types', function (Blueprint $table) {
            $table->id();
            $table->string('code', 20)->unique();
            $table->string('name', 100);
            $table->text('description')->nullable();
            $table->enum('category', ['job_costing', 'roll_over']);
            $table->enum('stage', ['adonan', 'gelondongan', 'kerupuk_kg', 'packing']);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // Recreate bom_templates table
        Schema::create('bom_templates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('bom_type_id')->constrained()->onDelete('restrict');
            $table->string('code', 20)->unique();
            $table->string('name', 100);
            $table->text('description')->nullable();
            $table->foreignId('output_item_id')->constrained('items')->onDelete('restrict');
            $table->decimal('output_quantity', 10, 3)->default(1);
            $table->string('output_unit', 15)->nullable();
            $table->integer('version')->default(1);
            $table->foreignId('created_by')->constrained('users')->onDelete('restrict');
            $table->boolean('is_active')->default(true);
            $table->boolean('is_template')->default(false);
            $table->foreignId('parent_template_id')->nullable()->constrained('bom_templates')->onDelete('set null');
            $table->timestamps();

            $table->index(['bom_type_id', 'is_active']);
            $table->index(['output_item_id', 'is_active']);
            $table->index('created_by');
        });

        // Recreate bom_ingredients table
        Schema::create('bom_ingredients', function (Blueprint $table) {
            $table->id();
            $table->foreignId('bom_template_id')->constrained()->onDelete('cascade');
            $table->foreignId('ingredient_item_id')->constrained('items')->onDelete('restrict');
            $table->decimal('quantity', 10, 3);
            $table->string('unit', 15)->nullable();
            $table->integer('sort_order')->default(0);
            $table->timestamps();

            $table->index(['bom_template_id', 'sort_order']);
            $table->index('ingredient_item_id');
            $table->unique(['bom_template_id', 'ingredient_item_id']);
        });
    }
};
