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
        Schema::create('cleaning_requests', function (Blueprint $table) {
            $table->id();
            $table->string('request_number')->unique();
            $table->string('requester_name');
            $table->string('requester_phone');
            $table->foreignId('requester_user_id')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('location_id')->constrained()->onDelete('cascade');
            $table->string('request_type'); // cleaning, repair
            $table->text('description');
            $table->string('photo')->nullable();
            $table->string('status')->default('pending'); // pending, in-progress, completed, rejected
            $table->foreignId('handled_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('handled_at')->nullable();
            $table->text('handling_notes')->nullable();
            $table->timestamps();
            
            $table->index(['status', 'request_type']);
            $table->index(['location_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cleaning_requests');
    }
};
