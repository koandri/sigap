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
        Schema::create('asset_components', function (Blueprint $table) {
            $table->id();
            $table->foreignId('parent_asset_id')->constrained('assets')->onDelete('cascade');
            $table->foreignId('component_asset_id')->constrained('assets')->onDelete('restrict');
            $table->string('component_type'); // ComponentType enum
            $table->date('installed_date');
            $table->decimal('installed_usage_value', 15, 2)->nullable();
            $table->decimal('disposed_usage_value', 15, 2)->nullable();
            $table->date('removed_date')->nullable();
            $table->foreignId('removed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('removal_reason')->nullable();
            $table->text('installation_notes')->nullable();
            $table->timestamps();
            
            // Indexes for performance
            $table->index(['parent_asset_id', 'component_asset_id']);
            $table->index('component_type');
            $table->index('installed_date');
            $table->index('removed_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('asset_components');
    }
};
