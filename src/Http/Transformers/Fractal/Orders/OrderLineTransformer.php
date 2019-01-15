<?php

namespace GetCandy\Api\Http\Transformers\Fractal\Orders;

use GetCandy\Api\Core\Orders\Models\OrderLine;
use GetCandy\Api\Http\Transformers\Fractal\BaseTransformer;
use GetCandy\Api\Http\Transformers\Fractal\Products\ProductVariantTransformer;

class OrderLineTransformer extends BaseTransformer
{
    protected $availableIncludes = ['variant'];

    public function transform(OrderLine $line)
    {
        $data = [
            'id' => $line->encodedId(),
            'quantity' => $line->quantity,
            'line_total' => $line->line_total,
            'discount_total' => $line->discount_total,
            'unit_price' => $line->unit_price,
            'unit_qty' => $line->unit_qty,
            'tax_total' => $line->tax_total,
            'tax_rate' => $line->tax_rate,
            'description' => $line->description,
            'option' => $line->option,
            'sku' => $line->sku,
            'is_shipping' => (bool) $line->is_shipping,
            'is_manual' => (bool) $line->is_manual,
        ];

        return $data;
    }

    protected function includeVariant($line)
    {
        return $this->item($line->variant, new ProductVariantTransformer);
    }
}
