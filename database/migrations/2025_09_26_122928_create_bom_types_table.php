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
        Schema::create('bom_types', function (Blueprint $table) {
            $table->id();
            $table->string('code', 10)->unique(); // JC-ADN, RO-ADN, JC-PCK, RO-PCK
            $table->string('name', 50);
            $table->text('description');
            $table->enum('category', ['job_costing', 'roll_over']); // JC or RO
            $table->enum('stage', ['adonan', 'gelondongan', 'kerupuk_kg', 'packing']); // Production stage
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bom_types');
    }
};
