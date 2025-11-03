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
        Schema::create('recipes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('dough_item_id')->constrained('items')->onDelete('restrict'); // References Item (e.g., "Adonan Surya Bintang Kuning")
            $table->string('name', 100); // e.g., "Adonan Surya Bintang Kuning"
            $table->date('recipe_date'); // Date when recipe was created/effective
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true); // Active recipes shown in dropdown
            $table->foreignId('created_by')->constrained('users')->onDelete('restrict');
            $table->timestamps();

            $table->index(['dough_item_id', 'is_active']);
            $table->index('recipe_date');
            $table->index('created_by');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('recipes');
    }
};
