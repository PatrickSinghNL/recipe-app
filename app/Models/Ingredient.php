<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Ingredient extends Model
{
    protected $fillable = [
        'name',
        'quantity',
        'price',
        'image',
        'store_id',
    ];

    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class);
    }

    public function recipes(): BelongsToMany
    {
        return $this->belongsToMany(Recipe::class)->withPivot('quantity');
    }
}
