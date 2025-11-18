<?php

declare(strict_types=1);

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
            $table->string('field_code', 50);
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
                'boolean',
                'calculated',
                'hidden',
                'signature',
                'live_photo'
            ]);
            $table->boolean('is_required')->default(false);
            $table->json('validation_rules')->nullable();
            $table->json('api_source_config')->nullable();
            $table->json('conditional_logic')->nullable();
            $table->text('calculation_formula')->nullable();
            $table->json('calculation_dependencies')->nullable();
            $table->text('help_text')->nullable();
            $table->string('placeholder')->nullable();
            $table->integer('order_position')->default(0);
            $table->timestamps();
            
            $table->unique(['form_version_id', 'field_code']);
            $table->index('created_at');
            $table->index('order_position');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('form_fields');
    }
};
