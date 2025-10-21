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
        Schema::create('document_version_approvals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('document_version_id')->constrained()->onDelete('cascade');
            $table->foreignId('approver_id')->constrained('users');
            $table->string('approval_tier'); // manager, management_representative
            $table->string('status'); // pending, approved, rejected
            $table->text('notes')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('document_version_approvals');
    }
};
