<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        \App\Models\User::factory()->create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'password' => bcrypt('password'),
        ]);

        $ingredient1 = \App\Models\Ingredient::create(['name' => 'Pasta', 'quantity' => '200g', 'price' => 1.50]);
        $ingredient2 = \App\Models\Ingredient::create(['name' => 'Eggs', 'quantity' => '2 pieces', 'price' => 0.60]);
        $ingredient3 = \App\Models\Ingredient::create(['name' => 'Pancetta', 'quantity' => '100g', 'price' => 3.00]);
        $ingredient4 = \App\Models\Ingredient::create(['name' => 'Pecorino Romano', 'quantity' => '50g', 'price' => 2.00]);

        $supply1 = \App\Models\Supply::create(['name' => 'Frying Pan']);
        $supply2 = \App\Models\Supply::create(['name' => 'Pot']);

        $recipe = \App\Models\Recipe::create([
            'name' => 'Authentic Carbonara',
            'description' => 'A classic Roman pasta dish made with eggs, cheese, pancetta, and black pepper.',
            'time' => 20,
            'number_of_persons' => 2,
            'is_published' => true,
        ]);

        $recipe->ingredients()->attach([$ingredient1->id, $ingredient2->id, $ingredient3->id, $ingredient4->id]);
        $recipe->supplies()->attach([$supply1->id, $supply2->id]);
    }
}
