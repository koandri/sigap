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
        Schema::create('items', function (Blueprint $table) {
            $table->id();
            $table->string('accurate_id', 15)->unique();
            $table->string('shortname', 10)->nullable();
            $table->string('name', 100)->unique();
            $table->foreignId('item_category_id')->constrained()->onDelete('restrict');
            $table->string('unit', 15)->nullable();
            $table->string('merk', 15)->nullable();
            $table->smallInteger('qty_kg_per_pack')->default(1);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['item_category_id', 'is_active']);
            $table->index('accurate_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('items');
    }
};
