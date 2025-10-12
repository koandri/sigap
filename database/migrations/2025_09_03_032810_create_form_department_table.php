<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('form_department', function (Blueprint $table) {
            $table->id();
            $table->foreignId('form_id')->constrained()->onDelete('cascade');
            $table->unsignedBigInteger('department_id');
            $table->timestamps();
            
            $table->unique(['form_id', 'department_id']);
            $table->foreign('department_id')->references('id')->on('departments')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('form_department');
    }
};