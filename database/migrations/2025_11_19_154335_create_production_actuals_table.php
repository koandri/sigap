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
        Schema::create('production_actuals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('production_plan_id')->unique()->constrained('production_plans')->onDelete('cascade');
            $table->date('production_date'); // When production actually happened
            $table->foreignId('recorded_by')->constrained('users')->onDelete('restrict');
            $table->timestamp('recorded_at');
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index('production_plan_id');
            $table->index('production_date');
            $table->index('recorded_by');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('production_actuals');
    }
};
