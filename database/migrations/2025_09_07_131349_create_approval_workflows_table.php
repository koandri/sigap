<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('approval_workflows', function (Blueprint $table) {
            $table->id();
            $table->foreignId('form_id')->constrained()->onDelete('cascade');
            $table->string('workflow_name');
            $table->text('description')->nullable();
            $table->enum('flow_type', ['sequential', 'parallel'])->default('sequential');
            $table->boolean('is_active')->default(true);
            $table->json('conditions')->nullable(); // For conditional routing
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();
            
            $table->index(['form_id', 'is_active']);
            $table->foreign('created_by')->references('id')->on('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('approval_workflows');
    }
};