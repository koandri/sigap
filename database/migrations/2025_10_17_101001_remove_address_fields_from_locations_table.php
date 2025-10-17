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
        Schema::table('locations', function (Blueprint $table) {
            $table->dropColumn(['address', 'city', 'postal_code', 'phone']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('locations', function (Blueprint $table) {
            $table->string('address', 500)->nullable()->after('code');
            $table->string('city', 100)->nullable()->after('address');
            $table->string('postal_code', 20)->nullable()->after('city');
            $table->string('phone', 50)->nullable()->after('postal_code');
        });
    }
};
