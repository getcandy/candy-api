<?php

namespace GetCandy\Api\Core\Discounts\Models;

use GetCandy\Api\Core\Scaffold\BaseModel;

class DiscountReward extends BaseModel
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['value', 'type', 'product_id'];

    /**
     * The Hashid connection name for enconding the id.
     *
     * @var string
     */
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
