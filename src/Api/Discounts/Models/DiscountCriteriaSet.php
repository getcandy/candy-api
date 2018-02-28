<?php

namespace GetCandy\Api\Discounts\Models;

use GetCandy\Api\Scaffold\BaseModel;

class DiscountCriteriaSet extends BaseModel
{
    protected $fillable = ['scope', 'outcome'];

    protected $hashids = 'main';

    public function discount()
    {
        return $this->belongsTo(Discount::class);
    }

    public function items()
    {
        return $this->hasMany(DiscountCriteriaItem::class);
    }
}
