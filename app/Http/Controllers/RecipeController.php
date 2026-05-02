<?php

namespace App\Http\Controllers;

use App\Models\Recipe;
use Illuminate\Http\Request;
use Inertia\Inertia;

class RecipeController extends Controller
{
    public function index(Request $request)
    {
        $query = Recipe::where('is_published', true);

        if ($request->has('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        return Inertia::render('public/RecipeIndex', [
            'recipes' => $query->latest()->get(),
            'filters' => $request->only(['search']),
        ]);
    }

    public function show(Recipe $recipe)
    {
        if (!$recipe->is_published) {
            abort(404);
        }

        return Inertia::render('public/RecipeDetail', [
            'recipe' => $recipe->load(['ingredients', 'supplies']),
        ]);
    }
}
