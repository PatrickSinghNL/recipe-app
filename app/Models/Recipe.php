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

    public function ingredients(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(Ingredient::class)->withPivot('quantity');
    }

    public function supplies(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(Supply::class);
    }

    public function categories(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(Category::class);
    }
}
