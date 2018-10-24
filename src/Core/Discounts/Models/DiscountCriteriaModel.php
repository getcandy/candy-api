<?php

namespace GetCandy\Api\Core\Discounts\Models;

use GetCandy\Api\Core\Scaffold\BaseModel;

class DiscountCriteriaModel extends BaseModel
{
    public function criteria()
    {
        return $this->belongsTo(DiscountCriteriaItem::class, 'discount_criteria_item_id');
    }
}
