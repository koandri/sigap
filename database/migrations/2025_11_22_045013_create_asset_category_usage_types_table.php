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
        Schema::create('asset_category_usage_types', function (Blueprint $table) {
            $table->id();
            $table->foreignId('asset_category_id')->constrained('asset_categories')->onDelete('cascade');
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('lifetime_unit')->nullable();
            $table->decimal('expected_average_lifetime', 15, 2)->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            // Unique constraint: name must be unique per category
            $table->unique(['asset_category_id', 'name']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('asset_category_usage_types');
    }
};
