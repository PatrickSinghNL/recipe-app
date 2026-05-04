<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('category_recipe', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->constrained()->cascadeOnDelete();
            $table->foreignId('recipe_id')->constrained()->cascadeOnDelete();
            $table->timestamps();
        });

        // Transfer existing category_id from recipes to the pivot table
        if (Schema::hasColumn('recipes', 'category_id')) {
            $recipesWithCategories = DB::table('recipes')
                ->whereNotNull('category_id')
                ->get(['id', 'category_id']);

            foreach ($recipesWithCategories as $recipe) {
                DB::table('category_recipe')->insert([
                    'recipe_id' => $recipe->id,
                    'category_id' => $recipe->category_id,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            Schema::table('recipes', function (Blueprint $table) {
                $table->dropForeign(['category_id']);
                $table->dropColumn('category_id');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('recipes', function (Blueprint $table) {
            $table->foreignId('category_id')->nullable()->constrained()->nullOnDelete();
        });

        // Transfer one category back if needed (this is a bit lossy but better than nothing)
        $pivotEntries = DB::table('category_recipe')->get();
        foreach ($pivotEntries as $entry) {
            DB::table('recipes')
                ->where('id', $entry->recipe_id)
                ->update(['category_id' => $entry->category_id]);
        }

        Schema::dropIfExists('category_recipe');
    }
};
