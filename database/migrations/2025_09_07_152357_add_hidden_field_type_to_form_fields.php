<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Add hidden to field_type enum
        DB::statement("ALTER TABLE form_fields MODIFY COLUMN field_type ENUM(
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
            'hidden'
        )");
    }

    public function down(): void
    {
        // Revert enum (remove hidden)
        DB::statement("ALTER TABLE form_fields MODIFY COLUMN field_type ENUM(
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
            'calculated'
        )");
    }
};