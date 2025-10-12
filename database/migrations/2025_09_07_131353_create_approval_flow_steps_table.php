<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('approval_flow_steps', function (Blueprint $table) {
            $table->id();
            $table->foreignId('approval_workflow_id')->constrained()->onDelete('cascade');
            $table->integer('step_order');
            $table->string('step_name');
            $table->enum('approver_type', ['user', 'role', 'department']); // How to determine approver
            $table->unsignedBigInteger('approver_user_id')->nullable(); // Specific user
            $table->string('approver_role')->nullable(); // Role code
            $table->unsignedBigInteger('approver_department_id')->nullable(); // Department
            $table->integer('sla_hours')->nullable(); // SLA in hours
            $table->boolean('is_required')->default(true); // Can this step be skipped?
            $table->json('conditions')->nullable(); // Conditions for this step
            $table->timestamps();
            
            $table->index(['approval_workflow_id', 'step_order']);
            $table->foreign('approver_user_id')->references('id')->on('users')->nullOnDelete();
            $table->foreign('approver_department_id')->references('id')->on('departments')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('approval_flow_steps');
    }
};