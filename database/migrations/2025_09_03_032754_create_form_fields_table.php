<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('form_fields', function (Blueprint $table) {
            $table->id();
            $table->foreignId('form_version_id')->constrained()->onDelete('cascade');
            $table->string('field_code', 50); // Explained below
            $table->string('field_label');
            $table->enum('field_type', [
                'text_short',
                'text_long', 
                'number',
                'decimal',
                'date',
                'datetime',
                'select_single',
                'select_multiple',
                'radio',
                'checkbox',
                'file',
                'boolean'
            ]);
            $table->boolean('is_required')->default(false);
            $table->json('validation_rules')->nullable();
            $table->json('conditional_logic')->nullable();
            $table->text('help_text')->nullable();
            $table->string('placeholder')->nullable();
            $table->timestamps(); // Order by created_at for chronological
            
            $table->unique(['form_version_id', 'field_code']);
            $table->index('created_at'); // For chronological ordering
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('form_fields');
    }
};