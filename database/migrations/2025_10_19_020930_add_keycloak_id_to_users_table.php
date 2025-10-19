<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('keycloak_id')->nullable()->after('manager_id');
            $table->index('keycloak_id');
            $table->dropColumn('asana_id');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->bigInteger('asana_id')->nullable()->after('manager_id');
            $table->dropColumn('keycloak_id');
        });
    }
};