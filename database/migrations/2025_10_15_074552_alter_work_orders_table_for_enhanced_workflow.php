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
        Schema::table('work_orders', function (Blueprint $table) {
            // Add new columns
            $table->foreignId('assigned_by')->nullable()->constrained('users')->onDelete('set null')->after('requested_by');
            $table->timestamp('assigned_at')->nullable()->after('assigned_by');
            $table->timestamp('work_started_at')->nullable()->after('assigned_at');
            $table->timestamp('work_finished_at')->nullable()->after('work_started_at');
            $table->timestamp('verified_at')->nullable()->after('work_finished_at');
            $table->foreignId('verified_by')->nullable()->constrained('users')->onDelete('set null')->after('verified_at');
            $table->text('verification_notes')->nullable()->after('verified_by');
            
            // Update status enum
            $table->dropColumn('status');
        });
        
        Schema::table('work_orders', function (Blueprint $table) {
            $table->enum('status', [
                'submitted', 
                'assigned', 
                'in-progress', 
                'pending-verification', 
                'verified', 
                'completed', 
                'rework',
                'cancelled'
            ])->default('submitted')->after('priority');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('work_orders', function (Blueprint $table) {
            $table->dropColumn([
                'assigned_by',
                'assigned_at', 
                'work_started_at',
                'work_finished_at',
                'verified_at',
                'verified_by',
                'verification_notes'
            ]);
            
            $table->dropColumn('status');
        });
        
        Schema::table('work_orders', function (Blueprint $table) {
            $table->enum('status', [
                'pending',
                'in-progress', 
                'completed',
                'cancelled'
            ])->default('pending')->after('priority');
        });
    }
};