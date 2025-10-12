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
        Schema::dropIfExists('item_locations');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::create('item_locations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('warehouse_id')->constrained()->onDelete('cascade');
            $table->foreignId('item_id')->constrained()->onDelete('cascade');
            $table->string('location_name', 100)->nullable();
            $table->decimal('current_quantity', 10, 2)->default(0);
            $table->date('expiry_date')->nullable();
            $table->foreignId('last_updated_by')->constrained('users');
            $table->timestamp('last_updated_at');
            $table->timestamps();
            $table->unique(['warehouse_id', 'item_id', 'location_name']);
        });
    }
};