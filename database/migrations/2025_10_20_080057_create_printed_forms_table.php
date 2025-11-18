<?php

declare(strict_types=1);

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
        Schema::create('printed_forms', function (Blueprint $table) {
            $table->id();
            $table->foreignId('form_request_item_id')->constrained()->onDelete('cascade');
            $table->string('form_number')->unique(); // PF-YYMMDD-XXXX
            $table->foreignId('document_version_id')->constrained()->onDelete('cascade');
            $table->foreignId('issued_to')->constrained('users');
            $table->timestamp('issued_at');
            $table->string('status'); // issued, circulating, returned, lost, spoilt, received, scanned
            $table->timestamp('returned_at')->nullable();
            $table->timestamp('received_at')->nullable();
            $table->timestamp('scanned_at')->nullable();
            $table->string('scanned_file_path')->nullable();
            $table->text('notes')->nullable();
            $table->json('physical_location')->nullable(); // room_no, cabinet_no, shelf_no
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('printed_forms');
    }
};
