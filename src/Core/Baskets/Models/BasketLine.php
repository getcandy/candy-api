<?php

namespace GetCandy\Api\Core\Baskets\Models;

use GetCandy\Api\Core\Scaffold\BaseModel;
use GetCandy\Api\Core\Baskets\Models\Basket;
use GetCandy\Api\Core\Products\Models\ProductVariant;

class BasketLine extends BaseModel
{
    protected $hashids = 'basket';

    protected $fillable = ['quantity', 'product_variant_id', 'total'];

    public function variant()
    {
        return $this->belongsTo(ProductVariant::class, 'product_variant_id');
    }

    public function basket()
    {
        return $this->belongsTo(Basket::class);
    }
}
