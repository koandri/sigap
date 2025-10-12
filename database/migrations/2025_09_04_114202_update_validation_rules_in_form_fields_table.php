<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Check if validation_rules already exists
        if (!Schema::hasColumn('form_fields', 'validation_rules')) {
            Schema::table('form_fields', function (Blueprint $table) {
                $table->json('validation_rules')->nullable()->after('is_required');
            });
        }
    }

    public function down(): void
    {
        Schema::table('form_fields', function (Blueprint $table) {
            if (Schema::hasColumn('form_fields', 'validation_rules')) {
                $table->dropColumn('validation_rules');
            }
        });
    }
};