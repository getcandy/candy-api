<?php

namespace GetCandy\Api\Http\Resources\Orders;

use GetCandy\Api\Http\Resources\AbstractResource;

class OrderDiscountResource extends AbstractResource
{
    public function payload()
    {
        return [
            'id' => $this->encoded_id,
            'name' => $this->name,
            'type' => $this->type,
            'coupon' => $this->coupon,
            'amount' => $this->amount,
        ];
    }
}
