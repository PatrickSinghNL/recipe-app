<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\Request;
use Inertia\Inertia;

class SettingController extends Controller
{
    public function index()
    {
        return Inertia::render('admin/settings/Index', [
            'settings' => [
                'registration_enabled' => Setting::isEnabled('registration_enabled'),
            ],
        ]);
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'registration_enabled' => 'required|boolean',
        ]);

        Setting::set('registration_enabled', $validated['registration_enabled']);

        return back()->with('success', 'Settings updated.');
    }
}
