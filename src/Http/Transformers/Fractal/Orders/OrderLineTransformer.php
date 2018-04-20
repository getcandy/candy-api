<?php

namespace GetCandy\Api\Http\Transformers\Fractal\Orders;

use GetCandy\Api\Core\Orders\Models\OrderLine;
use GetCandy\Api\Http\Transformers\Fractal\BaseTransformer;
use GetCandy\Api\Http\Transformers\Fractal\Products\ProductVariantTransformer;

class OrderLineTransformer extends BaseTransformer
{
    protected $availableIncludes = [
        'variant',
    ];

    public function transform(OrderLine $line)
    {
        $data = [
            'id' => $line->encodedId(),
            'quantity' => $line->quantity,
            'total' => round($line->total, 2),
            'product' => $line->product,
            'sku' => $line->sku,
            'variant' => $line->variant,
        ];

        return $data;
    }

    protected function includeVariant(OrderLine $line)
    {
        return $this->item($line->variant, new ProductVariantTransformer);
    }
}
