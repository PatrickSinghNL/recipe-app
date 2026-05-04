<?php

namespace Database\Seeders;

use App\Models\Ingredient;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use App\Models\Recipe;
use App\Models\Supply;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        User::factory()->create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'password' => bcrypt('password'),
            'is_approved' => 1,
            'is_admin' => 1,
        ]);

        $ingredient1 = Ingredient::create(['name' => 'Pasta', 'quantity' => '200g', 'price' => 1.50]);
        $ingredient2 = Ingredient::create(['name' => 'Eggs', 'quantity' => '2 pieces', 'price' => 0.60]);
        $ingredient3 = Ingredient::create(['name' => 'Pancetta', 'quantity' => '100g', 'price' => 3.00]);
        $ingredient4 = Ingredient::create(['name' => 'Pecorino Romano', 'quantity' => '50g', 'price' => 2.00]);

        $supply1 = Supply::create(['name' => 'Frying Pan']);
        $supply2 = Supply::create(['name' => 'Pot']);

        $recipe = Recipe::create([
            'name' => 'Authentic Carbonara',
            'description' => 'A classic Roman pasta dish made with eggs, cheese, pancetta, and black pepper.',
            'time' => 20,
            'number_of_persons' => 2,
            'is_published' => true,
        ]);

        $recipe->ingredients()->attach([$ingredient1->id, $ingredient2->id, $ingredient3->id, $ingredient4->id]);
        $recipe->supplies()->attach([$supply1->id, $supply2->id]);

        $recipe2 = Recipe::create([
            'name' => 'Italian Margherita Pizza',
            'description' => 'A simple yet delicious pizza with fresh tomatoes, mozzarella, and basil.',
            'time' => 45,
            'number_of_persons' => 4,
            'is_published' => true,
        ]);

        $ingredient5 = Ingredient::create(['name' => 'Pizza Dough', 'quantity' => '500g', 'price' => 2.00]);
        $ingredient6 = Ingredient::create(['name' => 'Mozzarella', 'quantity' => '250g', 'price' => 3.50]);
        $ingredient7 = Ingredient::create(['name' => 'Basil', 'quantity' => '1 bunch', 'price' => 1.00]);

        $recipe2->ingredients()->attach([$ingredient5->id, $ingredient6->id, $ingredient7->id]);
        $recipe2->supplies()->attach([$supply2->id]);
    }
}
