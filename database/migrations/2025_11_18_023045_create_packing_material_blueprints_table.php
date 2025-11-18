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
        Schema::create('packing_material_blueprints', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('pack_item_id')
                ->constrained('items')
                ->onDelete('cascade')
                ->name('pmb_pack_item_fk');
            $table->foreignId('material_item_id')
                ->constrained('items')
                ->onDelete('restrict')
                ->name('pmb_material_item_fk');
            $table->decimal('quantity_per_pack', 10, 3);
            $table->timestamps();

            $table->unique(
                ['pack_item_id', 'material_item_id'],
                'pmb_pack_material_unique'
            );
            $table->index('pack_item_id', 'pmb_pack_item_idx');
            $table->index('material_item_id', 'pmb_material_item_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('packing_material_blueprints');
    }
};
