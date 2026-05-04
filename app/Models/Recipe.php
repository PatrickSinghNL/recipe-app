<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Recipe extends Model
{
    protected $fillable = [
        'name',
        'description',
        'time',
        'number_of_persons',
        'image',
        'is_published',
    ];

    public function ingredients()
    {
        return $this->belongsToMany(Ingredient::class)->withPivot('quantity');
    }

    public function supplies()
    {
        return $this->belongsToMany(Supply::class);
    }

    public function categories()
    {
        return $this->belongsToMany(Category::class);
    }
}
