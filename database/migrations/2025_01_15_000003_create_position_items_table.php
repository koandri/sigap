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
        Schema::create('position_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('shelf_position_id')->constrained()->onDelete('cascade');
            $table->foreignId('item_id')->constrained()->onDelete('cascade');
            $table->decimal('quantity', 10, 3)->default(0);
            $table->date('expiry_date')->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('last_updated_by')->constrained('users');
            $table->timestamp('last_updated_at');
            $table->timestamps();
            
            $table->unique(['shelf_position_id', 'item_id']);
            $table->index(['shelf_position_id', 'quantity']);
            $table->index(['expiry_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('position_items');
    }
};
