<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Supply;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Storage;

class SupplyController extends Controller
{
    public function index()
    {
        return Inertia::render('admin/supplies/Index', [
            'supplies' => Supply::latest()->get(),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'image' => 'nullable|image|max:2048',
        ]);

        if ($request->hasFile('image')) {
            $validated['image'] = $request->file('image')->store('supplies', 'public');
        }

        Supply::create($validated);

        return redirect()->back()->with('success', 'Supply added.');
    }

    public function update(Request $request, Supply $supply)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'image' => 'nullable|image|max:2048',
        ]);

        if ($request->hasFile('image')) {
            if ($supply->image) {
                Storage::disk('public')->delete($supply->image);
            }
            $validated['image'] = $request->file('image')->store('supplies', 'public');
        }

        $supply->update($validated);

        return redirect()->back()->with('success', 'Supply updated.');
    }

    public function destroy(Supply $supply)
    {
        if ($supply->image) {
            Storage::disk('public')->delete($supply->image);
        }
        $supply->delete();

        return redirect()->back()->with('success', 'Supply deleted.');
    }
}
