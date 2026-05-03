<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Ingredient;
use App\Models\Recipe;
use App\Models\Supply;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

class RecipeController extends Controller
{
    public function index()
    {
        return Inertia::render('admin/recipes/Index', [
            'recipes' => Recipe::latest()->get(),
        ]);
    }

    public function create()
    {
        return Inertia::render('admin/recipes/Form', [
            'ingredients' => Ingredient::all(),
            'supplies' => Supply::all(),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'time' => 'required|integer',
            'number_of_persons' => 'required|integer',
            'is_published' => 'required|boolean',
            'image' => 'nullable|image|max:2048',
            'ingredients' => 'nullable|array',
            'supplies' => 'nullable|array',
        ]);

        if ($request->hasFile('image')) {
            $validated['image'] = $request->file('image')->store('recipes', 'public');
        }

        $validated['is_published'] = $request->boolean('is_published');

        $recipe = Recipe::create($validated);

        if ($request->has('ingredients')) {
            $recipe->ingredients()->sync($request->ingredients);
        }

        if ($request->has('supplies')) {
            $recipe->supplies()->sync($request->supplies);
        }

        return redirect()->route('admin.recipes.index')->with('success', 'Recipe created.');
    }

    public function edit(Recipe $recipe)
    {
        return Inertia::render('admin/recipes/Form', [
            'recipe' => $recipe->load(['ingredients', 'supplies']),
            'ingredients' => Ingredient::all(),
            'supplies' => Supply::all(),
        ]);
    }

    public function update(Request $request, Recipe $recipe)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'time' => 'required|integer',
            'number_of_persons' => 'required|integer',
            'is_published' => 'required|boolean',
            'image' => 'nullable|image|max:2048',
            'ingredients' => 'nullable|array',
            'supplies' => 'nullable|array',
        ]);

        if ($request->hasFile('image')) {
            if ($recipe->image) {
                Storage::disk('public')->delete($recipe->image);
            }
            $validated['image'] = $request->file('image')->store('recipes', 'public');
        }

        $validated['is_published'] = $request->boolean('is_published');

        $recipe->update($validated);

        $recipe->ingredients()->sync($request->ingredients ?? []);
        $recipe->supplies()->sync($request->supplies ?? []);

        return redirect()->route('admin.recipes.index')->with('success', 'Recipe updated.');
    }

    public function destroy(Recipe $recipe)
    {
        if ($recipe->image) {
            Storage::disk('public')->delete($recipe->image);
        }
        $recipe->delete();

        return redirect()->route('admin.recipes.index')->with('success', 'Recipe deleted.');
    }
}
