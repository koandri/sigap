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
        Schema::create('work_order_photos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('work_order_id')->constrained()->onDelete('cascade');
            $table->foreignId('uploaded_by')->constrained('users')->onDelete('cascade');
            $table->string('photo_path');
            $table->enum('photo_type', ['initial', 'progress', 'before', 'after', 'issue'])->default('progress');
            $table->text('caption')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('work_order_photos');
    }
};