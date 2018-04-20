<?php

namespace GetCandy\Api\Orders\Models;

use GetCandy\Api\Scaffold\BaseModel;
use GetCandy\Api\Products\Models\ProductVariant;

class OrderLine extends BaseModel
{
    protected $hashids = 'order';

    protected $fillable = ['quantity', 'description', 'variant', 'line_amount', 'sku', 'tax', 'tax_rate', 'discount', 'shipping'];

    public function variant()
    {
        return $this->belongsTo(ProductVariant::class, 'product_variant_id');
    }
}
