<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('ingredient_store', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ingredient_id')->constrained()->cascadeOnDelete();
            $table->foreignId('store_id')->constrained()->cascadeOnDelete();
            $table->decimal('price', 8, 2)->nullable();
            $table->string('quantity')->nullable();
            $table->text('product_url')->nullable();
            $table->string('image')->nullable();
            $table->timestamps();

            $table->unique(['ingredient_id', 'store_id']);
        });

        // Migrate existing data from ingredients table to the pivot
        $ingredients = DB::table('ingredients')
            ->whereNotNull('store_id')
            ->get();

        foreach ($ingredients as $ingredient) {
            DB::table('ingredient_store')->insert([
                'ingredient_id' => $ingredient->id,
                'store_id' => $ingredient->store_id,
                'price' => $ingredient->price,
                'quantity' => $ingredient->quantity,
                'product_url' => $ingredient->product_url,
                'image' => $ingredient->image,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ingredient_store');
    }
};
