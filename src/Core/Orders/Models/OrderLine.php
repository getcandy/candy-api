<?php

namespace GetCandy\Api\Core\Orders\Models;

use GetCandy\Api\Core\Products\Models\ProductVariant;
use GetCandy\Api\Core\Scaffold\BaseModel;
use GetCandy\Api\Core\Traits\HasMeta;

class OrderLine extends BaseModel
{
    use HasMeta;

    /**
     * The Hashid connection name for enconding the id.
     *
     * @var string
     */
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
