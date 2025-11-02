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
        Schema::table('printed_forms', function (Blueprint $table) {
            $table->json('physical_location')->nullable()->after('scanned_file_path'); // room_no, cabinet_no, shelf_no
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('printed_forms', function (Blueprint $table) {
            $table->dropColumn('physical_location');
        });
    }
};
