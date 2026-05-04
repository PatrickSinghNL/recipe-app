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
                'currency_name' => Setting::get('currency_name', 'Euro'),
                'currency_symbol' => Setting::get('currency_symbol', '€'),
            ],
        ]);
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'registration_enabled' => 'required|boolean',
            'currency_name' => 'required|string|max:50',
            'currency_symbol' => 'required|string|max:10',
        ]);

        Setting::set('registration_enabled', $validated['registration_enabled']);
        Setting::set('currency_name', $validated['currency_name']);
        Setting::set('currency_symbol', $validated['currency_symbol']);

        return back()->with('success', 'Settings updated.');
    }
}
