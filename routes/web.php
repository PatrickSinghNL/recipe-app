<?php

use App\Http\Controllers\RecipeController;
use App\Http\Controllers\Admin\RecipeController as AdminRecipeController;
use App\Http\Controllers\Admin\IngredientController;
use App\Http\Controllers\Admin\SupplyController;
use Illuminate\Support\Facades\Route;
use Laravel\Fortify\Features;

Route::get('/', function () {
    return redirect()->route('recipes.index');
})->name('home');

Route::get('/recipes', [RecipeController::class, 'index'])->name('recipes.index');
Route::get('/recipes/{recipe}', [RecipeController::class, 'show'])->name('recipes.show');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::inertia('dashboard', 'Dashboard')->name('dashboard');

    Route::prefix('admin')->name('admin.')->group(function () {
        Route::resource('recipes', AdminRecipeController::class);
        Route::resource('ingredients', IngredientController::class);
        Route::resource('supplies', SupplyController::class);
    });
});

require __DIR__.'/settings.php';
