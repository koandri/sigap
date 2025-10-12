<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('form_answers', function (Blueprint $table) {
            // Change answer_value from TEXT to LONGTEXT to handle longer file paths
            $table->longText('answer_value')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('form_answers', function (Blueprint $table) {
            // Revert back to TEXT
            $table->text('answer_value')->nullable()->change();
        });
    }
};