<?php

namespace GetCandy\Api\Core\Orders\Models;

use GetCandy\Api\Core\Traits\HasMeta;
use GetCandy\Api\Core\Scaffold\BaseModel;
use GetCandy\Api\Core\Products\Models\ProductVariant;

class OrderLine extends BaseModel
{
    use HasMeta;

    protected $hashids = 'order';

    protected $guarded = [];

    public function variant()
    {
        return $this->belongsTo(ProductVariant::class, 'product_variant_id', 'id');
    }

    public function order()
    {
        return $this->belongsTo(Order::class);
    }
}
