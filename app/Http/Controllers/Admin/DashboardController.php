<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Ingredient;
use App\Models\Recipe;
use App\Models\Store;
use App\Models\Supply;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function __invoke()
    {
        return Inertia::render('Dashboard', [
            'stats' => [
                'total_recipes' => Recipe::count(),
                'total_ingredients' => Ingredient::count(),
                'total_supplies' => Supply::count(),
                'total_stores' => Store::count(),
            ],
        ]);
    }
}
