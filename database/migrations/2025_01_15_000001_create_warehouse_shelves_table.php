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
        Schema::create('warehouse_shelves', function (Blueprint $table) {
            $table->id();
            $table->foreignId('warehouse_id')->constrained()->onDelete('cascade');
            $table->string('shelf_code', 10); // A-01, A-02, B-01, etc.
            $table->string('shelf_name', 50); // "Section A-01"
            $table->text('description')->nullable();
            $table->integer('max_capacity')->default(5); // 5 positions per section
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0);
            $table->timestamps();
            
            $table->unique(['warehouse_id', 'shelf_code']);
            $table->index(['warehouse_id', 'is_active']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('warehouse_shelves');
    }
};
