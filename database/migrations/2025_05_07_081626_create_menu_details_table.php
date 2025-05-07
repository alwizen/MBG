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
        Schema::create('menu_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('daily_menu_id')->constrained()->onDelete('cascade');
            $table->foreignId('item_id')->constrained();
            $table->decimal('quantity', 10, 2);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('menu_details');
    }
};
