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
        Schema::create('document_access_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('document_version_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('access_type'); // one_time, multiple
            $table->timestamp('requested_expiry_date')->nullable();
            $table->foreignId('approved_by')->nullable()->constrained('users');
            $table->string('approved_access_type')->nullable();
            $table->timestamp('approved_expiry_date')->nullable();
            $table->string('status'); // pending, approved, rejected
            $table->timestamp('requested_at');
            $table->timestamp('approved_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('document_access_requests');
    }
};
