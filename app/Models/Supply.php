<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Supply extends Model
{
    protected $fillable = [
        'name',
        'image',
    ];

    public function recipes()
    {
        return $this->belongsToMany(Recipe::class);
    }
}
