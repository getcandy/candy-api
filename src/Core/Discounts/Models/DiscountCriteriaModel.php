<?php

namespace GetCandy\Api\Core\Discounts\Models;

use GetCandy\Api\Core\Scaffold\BaseModel;
use GetCandy\Api\Core\Products\Models\Product;
use GetCandy\Api\Core\Customers\Models\CustomerGroup;

class DiscountCriteriaModel extends BaseModel
{
    public function criteria()
    {
        return $this->belongsTo(DiscountCriteriaItem::class, 'discount_criteria_item_id');
    }
}
