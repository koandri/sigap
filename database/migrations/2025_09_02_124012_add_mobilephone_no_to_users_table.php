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
        Schema::table('users', function (Blueprint $table) {
            $table->string('mobilephone_no', 16)->nullable()->after('email');
            $table->unsignedBigInteger('manager_id')->nullable()->after('remember_token');
            $table->foreign('manager_id')->references('id')->on('users');
            $table->bigInteger('asana_id')->nullable()->after('manager_id');
            $table->json('locations')->nullable()->after('asana_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'locations',
                'asana_id',
                'manager_id',
                'mobilephone_no'
            ]);
        });
    }
};
