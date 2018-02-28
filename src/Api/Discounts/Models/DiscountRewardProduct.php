<?php
namespace GetCandy\Api\Discounts\Models;

use GetCandy\Api\Scaffold\BaseModel;

class DiscountRewardProduct extends BaseModel
{
    protected $fillable = ['product_id', 'quantity'];

    protected $hashids = 'main';

    public function reward()
    {
        return $this->belongsTo(DiscountReward::class);
    }
}
