<?php

namespace GetCandy\Api\Http\Transformers\Fractal\Orders;

use GetCandy\Api\Core\Orders\Models\OrderLine;
use GetCandy\Api\Http\Transformers\Fractal\BaseTransformer;

class OrderLineTransformer extends BaseTransformer
{
    public function transform(OrderLine $line)
    {
        $data = [
            'id' => $line->encodedId(),
            'quantity' => $line->quantity,
            'line_amount' => round($line->line_amount, 2),
            'discount' => round($line->discount, 2),
            'tax' => round($line->tax, 2),
            'tax_rate' => $line->tax_rate,
            'description' => $line->description,
            'sku' => $line->sku,
            'variant' => $line->variant,
            'shipping' => (bool) $line->shipping,
        ];

        return $data;
    }
}
