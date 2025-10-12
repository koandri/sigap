<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Add new field type to enum
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
        
        // Add calculation_formula column
        Schema::table('form_fields', function (Blueprint $table) {
            $table->text('calculation_formula')->nullable()->after('conditional_logic');
            $table->json('calculation_dependencies')->nullable()->after('calculation_formula');
        });
    }

    public function down(): void
    {
        Schema::table('form_fields', function (Blueprint $table) {
            $table->dropColumn(['calculation_formula', 'calculation_dependencies']);
        });
        
        // Revert enum
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
            'boolean'
        )");
    }
};