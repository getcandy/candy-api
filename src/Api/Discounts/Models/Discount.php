<?php

namespace GetCandy\Api\Discounts\Models;

use GetCandy\Api\Scaffold\BaseModel;
use GetCandy\Api\Traits\HasAttributes;
use GetCandy\Api\Traits\HasChannels;

class Discount extends BaseModel
{
    use HasAttributes,
        HasChannels;

    protected $hashids = 'main';

    public function sets()
    {
        return $this->hasMany(DiscountCriteriaSet::class);
    }

    public function rewards()
    {
        return $this->hasMany(DiscountReward::class);
    }
}
