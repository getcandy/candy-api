<?php

namespace GetCandy\Api\Core\Discounts\Models;

use GetCandy\Api\Core\Scaffold\BaseModel;
use GetCandy\Api\Core\Traits\HasChannels;
use GetCandy\Api\Core\Traits\HasAttributes;

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

    public function items()
    {
        return $this->hasManyThrough(DiscountCriteriaItem::class, DiscountCriteriaSet::class);
    }
}
