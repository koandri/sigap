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
        Schema::create('kerupuk_pack_configurations', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('kerupuk_kg_item_id')
                ->constrained('items')
                ->onDelete('cascade')
                ->name('kpc_kerupuk_kg_fk');
            $table->foreignId('pack_item_id')
                ->constrained('items')
                ->onDelete('cascade')
                ->name('kpc_pack_item_fk');
            $table->decimal('qty_kg_per_pack', 10, 2)
                ->comment('Quantity of Kerupuk Kg (in kg) required to produce one pack');
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(
                ['kerupuk_kg_item_id', 'pack_item_id'],
                'kpc_kerupuk_pack_unique'
            );
            $table->index('kerupuk_kg_item_id', 'kpc_kerupuk_kg_idx');
            $table->index('pack_item_id', 'kpc_pack_item_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('kerupuk_pack_configurations');
    }
};
