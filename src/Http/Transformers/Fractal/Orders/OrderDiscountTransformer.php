<?php

namespace GetCandy\Api\Http\Transformers\Fractal\Orders;

use GetCandy\Api\Core\Orders\Models\OrderDiscount;
use GetCandy\Api\Http\Transformers\Fractal\BaseTransformer;

class OrderDiscountTransformer extends BaseTransformer
{
    protected $availableIncludes = [
        'order',
    ];

    public function transform(OrderDiscount $discount)
    {
        $data = [
            'id' => $discount->encodedId(),
            'name' => $discount->name,
            'coupon' => $discount->coupon,
            'amount' => $discount->amount,
            'type' => $discount->type,
        ];

        return $data;
    }

    protected function includeVariant(OrderDiscount $discount)
    {
        return $this->item($discount->order, new OrderTransformer);
    }
}
