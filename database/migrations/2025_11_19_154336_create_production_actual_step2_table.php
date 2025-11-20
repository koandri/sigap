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
        Schema::create('production_actual_step2', function (Blueprint $table) {
            $table->id();
            $table->foreignId('production_actual_id')->constrained('production_actuals')->onDelete('cascade');
            $table->foreignId('production_plan_step2_id')->constrained('production_plan_step2')->onDelete('cascade');
            $table->foreignId('adonan_item_id')->constrained('items')->onDelete('restrict');
            $table->foreignId('gelondongan_item_id')->constrained('items')->onDelete('restrict');
            $table->integer('actual_qty_gl1_adonan')->default(0);
            $table->integer('actual_qty_gl1_gelondongan')->default(0);
            $table->integer('actual_qty_gl2_adonan')->default(0);
            $table->integer('actual_qty_gl2_gelondongan')->default(0);
            $table->integer('actual_qty_ta_adonan')->default(0);
            $table->integer('actual_qty_ta_gelondongan')->default(0);
            $table->integer('actual_qty_bl_adonan')->default(0);
            $table->integer('actual_qty_bl_gelondongan')->default(0);
            $table->timestamp('recorded_at');
            $table->timestamps();

            $table->index('production_actual_id');
            $table->index('production_plan_step2_id');
            $table->index('adonan_item_id');
            $table->index('gelondongan_item_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('production_actual_step2');
    }
};
