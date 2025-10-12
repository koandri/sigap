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
        Schema::create('item_locations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('item_id')->constrained()->onDelete('cascade');
            $table->foreignId('warehouse_id')->constrained()->onDelete('cascade');
            $table->string('shelf_area', 20)->nullable(); // Shelf A1, Rack B2, etc.
            $table->decimal('current_quantity', 10, 2)->default(0); // Manual quantity updates
            $table->date('expiry_date'); // For perishable items
            $table->foreignId('last_updated_by')->constrained('users')->onDelete('restrict');
            $table->timestamp('last_updated_at')->useCurrent();
            $table->timestamps();

            $table->unique(['item_id', 'warehouse_id', 'shelf_area']);
            $table->index(['warehouse_id', 'item_id']);
            $table->index('expiry_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('item_locations');
    }
};
