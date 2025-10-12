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
        Schema::create('bom_templates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('bom_type_id')->constrained()->onDelete('restrict');
            $table->string('code', 20)->unique(); // ADN-001, GEL-001, etc.
            $table->string('name', 100);
            $table->text('description')->nullable();
            $table->foreignId('output_item_id')->constrained('items')->onDelete('restrict'); // What this BoM produces
            $table->decimal('output_quantity', 10, 3)->default(1); // How much it produces
            $table->string('output_unit', 15)->nullable(); // Unit of output
            $table->integer('version')->default(1);
            $table->foreignId('created_by')->constrained('users')->onDelete('restrict');
            $table->boolean('is_active')->default(true);
            $table->boolean('is_template')->default(false); // Can be used as base for new BoMs
            $table->foreignId('parent_template_id')->nullable()->constrained('bom_templates')->onDelete('set null'); // If copied from another template
            $table->timestamps();

            $table->index(['bom_type_id', 'is_active']);
            $table->index(['output_item_id', 'is_active']);
            $table->index('created_by');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bom_templates');
    }
};
