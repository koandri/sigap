<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('warehouses', function (Blueprint $table) {
            $table->id();
            $table->string('code', 10)->unique();
            $table->string('name', 50);
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index('code');
        });

        // Insert default warehouses
        $warehouses = [
            [
                'code' => 'WH-RM',
                'name' => 'Raw Materials Storage',
                'description' => 'Storage for raw materials (flour, spices, oil, etc.)',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'code' => 'WH-CS',
                'name' => 'Cold Storage',
                'description' => 'Cold storage for gelondongans and perishable items',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'code' => 'WH-DS',
                'name' => 'Dry Storage',
                'description' => 'Dry storage for dried crackers and finished products',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'code' => 'WH-PA',
                'name' => 'Packing Area',
                'description' => 'Area for packing and ready-to-ship products',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'code' => 'WH-PM',
                'name' => 'Packing Materials',
                'description' => 'Storage for packing materials (bags, boxes, labels)',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        foreach ($warehouses as $warehouse) {
            DB::table('warehouses')->insert($warehouse);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('warehouses');
    }
};
