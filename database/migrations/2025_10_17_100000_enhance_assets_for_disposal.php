<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Add 'disposed' to status enum
        DB::statement("ALTER TABLE assets MODIFY COLUMN status ENUM('operational', 'down', 'maintenance', 'disposed') DEFAULT 'operational'");
        
        // Add disposal tracking fields
        Schema::table('assets', function (Blueprint $table) {
            $table->date('disposed_date')->nullable()->after('is_active');
            $table->text('disposal_reason')->nullable()->after('disposed_date');
            $table->foreignId('disposed_by')->nullable()->after('disposal_reason')->constrained('users')->onDelete('set null');
            $table->foreignId('disposal_work_order_id')->nullable()->after('disposed_by')->constrained('work_orders')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('assets', function (Blueprint $table) {
            $table->dropForeign(['disposed_by']);
            $table->dropForeign(['disposal_work_order_id']);
            $table->dropColumn(['disposed_date', 'disposal_reason', 'disposed_by', 'disposal_work_order_id']);
        });
        
        DB::statement("ALTER TABLE assets MODIFY COLUMN status ENUM('operational', 'down', 'maintenance') DEFAULT 'operational'");
    }
};

