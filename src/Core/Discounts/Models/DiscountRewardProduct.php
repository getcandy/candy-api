<?php

namespace GetCandy\Api\Core\Discounts\Models;

use GetCandy\Api\Core\Products\Models\Product;
use GetCandy\Api\Core\Scaffold\BaseModel;

class DiscountRewardProduct extends BaseModel
{
    protected $fillable = ['product_id', 'quantity'];

    protected $hashids = 'main';

    public function reward()
    {
        return $this->belongsTo(DiscountReward::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
