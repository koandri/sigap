<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('form_field_options', function (Blueprint $table) {
            $table->id();
            $table->foreignId('form_field_id')->constrained()->onDelete('cascade');
            $table->string('option_value', 255);
            $table->string('option_label', 255);
            $table->boolean('is_default')->default(false);
            $table->timestamps(); // Order by created_at for chronological
            
            $table->index('form_field_id');
            $table->index('created_at'); // For chronological ordering
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('form_field_options');
    }
};