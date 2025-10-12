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
        Schema::create('item_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name', 30)->unique();
            $table->text('description')->nullable();
            $table->timestamps();
        });

        // Insert default categories
        $categories = [
            [
                'name' => 'Raw Materials',
                'description' => 'Basic materials used in production (flour, spices, oil, etc.)',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Adonan',
                'description' => 'Raw dough/mixture (intermediate product)',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Gelondongan',
                'description' => 'Shaped/formed crackers (intermediate product)',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Finished Products',
                'description' => 'Final products ready for sale',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Packing Materials',
                'description' => 'Materials used for packaging products',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        foreach ($categories as $category) {
            DB::table('item_categories')->insert($category);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('item_categories');
    }
};
