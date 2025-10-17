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
        Schema::create('cleaning_schedule_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cleaning_schedule_id')->constrained()->onDelete('cascade');
            $table->foreignId('asset_id')->nullable()->constrained()->onDelete('set null');
            $table->string('item_name');
            $table->text('item_description')->nullable();
            $table->integer('order')->default(0);
            $table->timestamps();
            
            $table->index(['cleaning_schedule_id', 'order']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cleaning_schedule_items');
    }
};
