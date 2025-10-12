<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('form_fields', function (Blueprint $table) {
            $table->integer('order_position')->default(0)->after('is_required');
            $table->index('order_position');
        });
        
        // Auto-populate order_position based on created_at for existing records
        DB::statement('
            UPDATE form_fields f1
            JOIN (
                SELECT id, 
                       @row_number := IF(@prev_version = form_version_id, @row_number + 1, 1) AS rn,
                       @prev_version := form_version_id
                FROM form_fields
                CROSS JOIN (SELECT @row_number := 0, @prev_version := 0) AS vars
                ORDER BY form_version_id, created_at
            ) f2 ON f1.id = f2.id
            SET f1.order_position = f2.rn * 10
        ');
    }

    public function down(): void
    {
        Schema::table('form_fields', function (Blueprint $table) {
            $table->dropIndex(['order_position']);
            $table->dropColumn('order_position');
        });
    }
};