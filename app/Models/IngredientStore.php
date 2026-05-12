<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;

class IngredientStore extends Pivot
{
    protected $table = 'ingredient_store';

    /**
     * @var string|null
     */
    public $image;

    /**
     * @var float|null
     */
    public $price;

    /**
     * @var string|null
     */
    public $quantity;

    /**
     * @var string|null
     */
    public $product_url;
}
