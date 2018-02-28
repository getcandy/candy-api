<?php
namespace GetCandy\Api\Discounts\Models;

use GetCandy\Api\Scaffold\BaseModel;

class DiscountReward extends BaseModel
{
    protected $fillable = ['value', 'type', 'product_id'];

    protected $hashids = 'main';

    public function discount()
    {
        return $this->belongsTo(Discount::class);
    }

    public function products()
    {
        return $this->hasMany(DiscountRewardProduct::class);
    }
}
