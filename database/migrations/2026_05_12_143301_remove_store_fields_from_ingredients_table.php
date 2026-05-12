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
        Schema::table('ingredients', function (Blueprint $table) {
            $table->dropForeign(['store_id']);
            $table->dropColumn(['store_id', 'price', 'quantity', 'product_url', 'image']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ingredients', function (Blueprint $table) {
            $table->decimal('price', 8, 2)->nullable();
            $table->string('quantity')->nullable();
            $table->text('product_url')->nullable();
            $table->string('image')->nullable();
            $table->foreignId('store_id')->nullable()->constrained()->nullOnDelete();
        });
    }
};
