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
        Schema::create('production_actual_step1', function (Blueprint $table) {
            $table->id();
            $table->foreignId('production_actual_id')->constrained('production_actuals')->onDelete('cascade');
            $table->foreignId('production_plan_step1_id')->constrained('production_plan_step1')->onDelete('cascade');
            $table->foreignId('dough_item_id')->constrained('items')->onDelete('restrict');
            $table->integer('actual_qty_gl1')->default(0);
            $table->integer('actual_qty_gl2')->default(0);
            $table->integer('actual_qty_ta')->default(0);
            $table->integer('actual_qty_bl')->default(0);
            $table->timestamp('recorded_at');
            $table->timestamps();

            $table->index('production_actual_id');
            $table->index('production_plan_step1_id');
            $table->index('dough_item_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('production_actual_step1');
    }
};
