<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * @property IngredientStore $pivot
 */
class Store extends Model
{
    protected $fillable = [
        'name',
        'image',
    ];

    public function ingredients(): BelongsToMany
    {
        return $this->belongsToMany(Ingredient::class)
            ->using(IngredientStore::class)
            ->withPivot('price', 'quantity', 'product_url', 'image')
            ->withTimestamps();
    }
}
