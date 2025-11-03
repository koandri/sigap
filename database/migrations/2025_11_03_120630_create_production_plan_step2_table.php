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
        Schema::create('production_plan_step2', function (Blueprint $table) {
            $table->id();
            $table->foreignId('production_plan_id')->constrained('production_plans')->onDelete('cascade');
            $table->foreignId('adonan_item_id')->constrained('items')->onDelete('restrict');
            $table->foreignId('gelondongan_item_id')->constrained('items')->onDelete('restrict');
            $table->decimal('qty_gl1_adonan', 10, 3)->default(0);
            $table->decimal('qty_gl1_gelondongan', 10, 3)->default(0);
            $table->decimal('qty_gl2_adonan', 10, 3)->default(0);
            $table->decimal('qty_gl2_gelondongan', 10, 3)->default(0);
            $table->decimal('qty_ta_adonan', 10, 3)->default(0);
            $table->decimal('qty_ta_gelondongan', 10, 3)->default(0);
            $table->decimal('qty_bl_adonan', 10, 3)->default(0);
            $table->decimal('qty_bl_gelondongan', 10, 3)->default(0);
            $table->timestamps();

            $table->index('production_plan_id');
            $table->index('adonan_item_id');
            $table->index('gelondongan_item_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('production_plan_step2');
    }
};
