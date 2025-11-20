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
        Schema::create('production_plan_step1', function (Blueprint $table) {
            $table->id();
            $table->foreignId('production_plan_id')->constrained('production_plans')->onDelete('cascade');
            $table->foreignId('dough_item_id')->constrained('items')->onDelete('restrict');
            $table->foreignId('recipe_id')->nullable()->constrained('recipes')->onDelete('set null');
            $table->string('recipe_name', 100); // Snapshot of recipe name when plan was created
            $table->date('recipe_date'); // Snapshot of recipe date
            $table->integer('qty_gl1')->default(0);
            $table->integer('qty_gl2')->default(0);
            $table->integer('qty_ta')->default(0);
            $table->integer('qty_bl')->default(0);
            $table->timestamps();

            $table->index('production_plan_id');
            $table->index('dough_item_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('production_plan_step1');
    }
};
