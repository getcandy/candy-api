<?php

namespace GetCandy\Api\Http\Resources\Discounts;

use GetCandy\Api\Http\Resources\AbstractResource;
use GetCandy\Api\Http\Resources\Products\ProductResource;

class DiscountRewardProductResource extends AbstractResource
{
    public function payload()
    {
        return [
            'id' => $this->encoded_id,
            'quantity' => $this->quantity,
        ];
    }

    public function includes()
    {
        return [
            'product' => $this->include('product', ProductResource::class),
        ];
    }
}
