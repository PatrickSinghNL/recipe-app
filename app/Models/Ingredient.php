<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Ingredient extends Model
{
    protected $fillable = [
        'name',
        'quantity',
        'price',
        'image',
        'store_id',
    ];

    public function store()
    {
        return $this->belongsTo(Store::class);
    }

    public function recipes()
    {
        return $this->belongsToMany(Recipe::class)->withPivot('quantity');
    }
}
