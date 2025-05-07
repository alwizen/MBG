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
        Schema::create('material_request_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('material_request_id')->constrained()->onDelete('cascade');
            $table->foreignId('item_id')->constrained();
            $table->decimal('quantity', 10, 2);
            $table->decimal('approved_quantity', 10, 2)->nullable();
            $table->enum('status', ['pending', 'approved', 'rejected', 'partial'])->default('pending');
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('material_request_details');
    }
};
