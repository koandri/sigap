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
        Schema::create('shelf_positions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('warehouse_shelf_id')->constrained()->onDelete('cascade');
            $table->string('position_code', 2); // 00, 01, 02, 03, 04
            $table->string('position_name', 20); // "Main", "Position 1", etc.
            $table->integer('max_capacity')->default(1);
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0);
            $table->timestamps();
            
            $table->unique(['warehouse_shelf_id', 'position_code']);
            $table->index(['warehouse_shelf_id', 'is_active']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('shelf_positions');
    }
};
