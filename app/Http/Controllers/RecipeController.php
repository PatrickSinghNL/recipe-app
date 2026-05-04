<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Recipe;
use Illuminate\Http\Request;
use Inertia\Inertia;

class RecipeController extends Controller
{
    public function index(Request $request)
    {
        $query = Recipe::with('categories')->where('is_published', true);

        if ($request->has('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        if ($request->has('category') && $request->category !== '') {
            $query->whereHas('categories', function ($q) use ($request) {
                $q->where('slug', $request->category);
            });
        }

        return Inertia::render('public/RecipeIndex', [
            'recipes' => $query->latest()->get(),
            'categories' => Category::orderBy('name')->get(),
            'filters' => $request->only(['search', 'category']),
        ]);
    }

    public function show(Recipe $recipe)
    {
        if (!$recipe->is_published) {
            abort(404);
        }

        return Inertia::render('public/RecipeDetail', [
            'recipe' => $recipe->load(['ingredients', 'supplies', 'categories']),
        ]);
    }
}
