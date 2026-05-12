<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * @property IngredientStore $pivot
 */
class Ingredient extends Model
{
    protected $fillable = [
        'name',
    ];

    public function stores(): BelongsToMany
    {
        return $this->belongsToMany(Store::class)
            ->using(IngredientStore::class)
            ->withPivot('price', 'quantity', 'product_url', 'image')
            ->withTimestamps();
    }

    public function recipes(): BelongsToMany
    {
        return $this->belongsToMany(Recipe::class)->withPivot('quantity');
    }
}
