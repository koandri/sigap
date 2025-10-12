<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
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
            'hidden',
            'signature'
        )");
    }

    public function down(): void
    {
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
};