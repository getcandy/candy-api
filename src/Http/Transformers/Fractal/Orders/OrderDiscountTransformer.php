<?php
namespace GetCandy\Api\Http\Transformers\Fractal\Orders;

use Carbon\Carbon;
use GetCandy\Api\Orders\Models\OrderLine;
use GetCandy\Api\Orders\Models\OrderDiscount;
use GetCandy\Api\Http\Transformers\Fractal\BaseTransformer;
use GetCandy\Api\Http\Transformers\Fractal\Products\ProductVariantTransformer;

class OrderDiscountTransformer extends BaseTransformer
{
    protected $availableIncludes = [
        'order'
    ];

    public function transform(OrderDiscount $discount)
    {
        $data = [
            'id' => $discount->encodedId(),
            'name' => $discount->name,
            'coupon' => $discount->coupon,
            'amount' => $discount->amount,
            'type' => $discount->type
        ];
        return $data;
    }

    protected function includeVariant(OrderDiscount $discount)
    {
        return $this->item($discount->order, new OrderTransformer);
    }
}
