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
        Schema::create('yield_guidelines', function (Blueprint $table) {
            $table->id();
            $table->foreignId('from_item_id')->constrained('items')->onDelete('restrict');
            $table->foreignId('to_item_id')->constrained('items')->onDelete('restrict');
            $table->enum('from_stage', ['adonan', 'gelondongan', 'kerupuk_kg']);
            $table->enum('to_stage', ['gelondongan', 'kerupuk_kg', 'packing']);
            $table->decimal('yield_quantity', 10, 3);
            $table->timestamps();

            // Ensure unique yield guideline per item pair
            $table->unique(['from_item_id', 'to_item_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('yield_guidelines');
    }
};
