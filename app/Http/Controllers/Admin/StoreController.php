<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Store;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

class StoreController extends Controller
{
    public function index()
    {
        return Inertia::render('admin/stores/Index', [
            'stores' => Store::orderBy('name', 'asc')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'image' => 'nullable|image|max:2048',
        ]);

        if ($request->hasFile('image')) {
            $validated['image'] = $request->file('image')->store('stores', 'public');
        }

        Store::create($validated);

        return redirect()->back()->with('success', 'Store added.');
    }

    public function update(Request $request, Store $store)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'image' => 'nullable|image|max:2048',
        ]);

        if ($request->hasFile('image')) {
            if ($store->image) {
                Storage::disk('public')->delete($store->image);
            }
            $validated['image'] = $request->file('image')->store('stores', 'public');
        } else {
            unset($validated['image']);
        }

        $store->update($validated);

        return redirect()->back()->with('success', 'Store updated.');
    }

    public function destroy(Store $store)
    {
        if ($store->image) {
            Storage::disk('public')->delete($store->image);
        }
        $store->delete();

        return redirect()->back()->with('success', 'Store deleted.');
    }
}
