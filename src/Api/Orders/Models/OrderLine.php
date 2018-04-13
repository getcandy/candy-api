<?php

namespace GetCandy\Api\Orders\Models;

use GetCandy\Api\Scaffold\BaseModel;
use GetCandy\Api\Products\Models\ProductVariant;

class OrderLine extends BaseModel
{
    protected $hashids = 'order';

    protected $fillable = ['quantity', 'product', 'variant', 'total', 'sku'];

    public function variant()
    {
        return $this->belongsTo(ProductVariant::class, 'product_variant_id');
    }
}
