<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Ingredient;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Storage;

class IngredientController extends Controller
{
    public function index()
    {
        return Inertia::render('admin/ingredients/Index', [
            'ingredients' => Ingredient::latest()->get(),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'quantity' => 'nullable|string',
            'price' => 'nullable|numeric',
            'image' => 'nullable|image|max:2048',
        ]);

        if ($request->hasFile('image')) {
            $validated['image'] = $request->file('image')->store('ingredients', 'public');
        }

        Ingredient::create($validated);

        return redirect()->back()->with('success', 'Ingredient added.');
    }

    public function update(Request $request, Ingredient $ingredient)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'quantity' => 'nullable|string',
            'price' => 'nullable|numeric',
            'image' => 'nullable|image|max:2048',
        ]);

        if ($request->hasFile('image')) {
            if ($ingredient->image) {
                Storage::disk('public')->delete($ingredient->image);
            }
            $validated['image'] = $request->file('image')->store('ingredients', 'public');
        }

        $ingredient->update($validated);

        return redirect()->back()->with('success', 'Ingredient updated.');
    }

    public function destroy(Ingredient $ingredient)
    {
        if ($ingredient->image) {
            Storage::disk('public')->delete($ingredient->image);
        }
        $ingredient->delete();

        return redirect()->back()->with('success', 'Ingredient deleted.');
    }
}
