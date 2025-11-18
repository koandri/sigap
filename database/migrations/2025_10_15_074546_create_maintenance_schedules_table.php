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
        Schema::create('maintenance_schedules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('asset_id')->constrained()->onDelete('cascade');
            $table->foreignId('maintenance_type_id')->constrained()->onDelete('cascade');
            $table->string('frequency_type', 20)->default('daily');
            $table->json('frequency_config')->nullable();
            $table->integer('frequency_days')->nullable();
            $table->timestamp('last_performed_at')->nullable();
            $table->timestamp('next_due_date');
            $table->text('description');
            $table->json('checklist')->nullable();
            $table->foreignId('assigned_to')->nullable()->constrained('users')->onDelete('set null');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('maintenance_schedules');
    }
};
