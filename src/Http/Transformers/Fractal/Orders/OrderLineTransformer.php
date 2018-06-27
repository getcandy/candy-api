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
            'line_total' => $line->line_total,
            'discount_total' => $line->discount_total,
            'unit_price' => $line->unit_price,
            'tax_total' => $line->tax_total,
            'tax_rate' => $line->tax_rate,
            'description' => $line->description,
            'sku' => $line->sku,
            'variant' => $line->variant,
            'is_shipping' => (bool) $line->is_shipping,
        ];

        return $data;
    }
}
