<?php

namespace GetCandy\Api\Core\Orders\Models;

use GetCandy\Api\Core\Scaffold\BaseModel;

class OrderDiscount extends BaseModel
{
    protected $hashids = 'order';

    protected $fillable = ['order_id', 'coupon', 'name', 'description', 'amount', 'type'];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }
}
