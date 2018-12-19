<?php

namespace GetCandy\Api\Http\Resources\Orders;

use GetCandy\Api\Http\Resources\AbstractResource;
use GetCandy\Api\Http\Resources\Discounts\DiscountCollection;
use GetCandy\Api\Http\Resources\Products\ProductVariantCollection;
use GetCandy\Api\Http\Resources\Products\ProductVariantResource;

class OrderDiscountResource extends AbstractResource
{
    public function payload()
    {
        return [
            'id' => $this->encoded_id,
            'name' => $this->name,
            'type' => $this->type,
            'coupon' => $this->coupon,
            'amount' => $this->amount
        ];
    }
}