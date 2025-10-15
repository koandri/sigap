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
        Schema::create('assets', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('code')->unique();
            $table->foreignId('asset_category_id')->constrained()->onDelete('cascade');
            $table->string('location')->nullable();
            $table->date('purchase_date')->nullable();
            $table->date('warranty_expiry')->nullable();
            $table->string('serial_number')->nullable();
            $table->string('manufacturer')->nullable();
            $table->string('model')->nullable();
            $table->enum('status', ['operational', 'down', 'maintenance'])->default('operational');
            $table->json('specifications')->nullable();
            $table->string('image_path')->nullable();
            $table->foreignId('department_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('set null');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('assets');
    }
};
