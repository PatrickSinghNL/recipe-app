<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
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
            'recipes' => Recipe::with('categories')->orderBy('name', 'asc')->get(),
        ]);
    }

    public function create()
    {
        return Inertia::render('admin/recipes/Form', [
            'ingredients' => Ingredient::all(),
            'supplies' => Supply::all(),
            'categories' => Category::all(),
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
            'ingredients.*.id' => 'exists:ingredients,id',
            'ingredients.*.quantity' => 'nullable|string|max:255',
            'supplies' => 'nullable|array',
            'categories' => 'nullable|array',
            'categories.*' => 'exists:categories,id',
        ]);

        if ($request->hasFile('image')) {
            $validated['image'] = $request->file('image')->store('recipes', 'public');
        }

        $validated['is_published'] = $request->boolean('is_published');

        $recipe = Recipe::create($validated);

        if ($request->has('ingredients')) {
            $ingredients = collect($request->ingredients)->mapWithKeys(function ($item) {
                return [$item['id'] => ['quantity' => $item['quantity'] ?? null]];
            })->toArray();
            $recipe->ingredients()->sync($ingredients);
        }

        if ($request->has('supplies')) {
            $recipe->supplies()->sync($request->supplies);
        }

        if ($request->has('categories')) {
            $recipe->categories()->sync($request->categories);
        }

        return redirect()->route('admin.recipes.index')->with('success', 'Recipe created.');
    }

    public function edit(Recipe $recipe)
    {
        return Inertia::render('admin/recipes/Form', [
            'recipe' => $recipe->load(['ingredients', 'supplies', 'categories']),
            'ingredients' => Ingredient::all(),
            'supplies' => Supply::all(),
            'categories' => Category::all(),
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
            'ingredients.*.id' => 'exists:ingredients,id',
            'ingredients.*.quantity' => 'nullable|string|max:255',
            'supplies' => 'nullable|array',
            'categories' => 'nullable|array',
            'categories.*' => 'exists:categories,id',
        ]);

        if ($request->hasFile('image')) {
            if ($recipe->image) {
                Storage::disk('public')->delete($recipe->image);
            }
            $validated['image'] = $request->file('image')->store('recipes', 'public');
        } else {
            unset($validated['image']);
        }

        $validated['is_published'] = $request->boolean('is_published');

        $recipe->update($validated);

        $ingredients = collect($request->ingredients ?? [])->mapWithKeys(function ($item) {
            return [$item['id'] => ['quantity' => $item['quantity'] ?? null]];
        })->toArray();
        $recipe->ingredients()->sync($ingredients);
        $recipe->supplies()->sync($request->supplies ?? []);
        $recipe->categories()->sync($request->categories ?? []);

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
