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
        Schema::create('cleaning_schedules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('location_id')->constrained()->onDelete('cascade');
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('frequency_type'); // daily, weekly, monthly
            $table->json('frequency_config')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            
            $table->index(['location_id', 'is_active']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cleaning_schedules');
    }
};
