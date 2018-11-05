<?php

namespace GetCandy\Api\Http\Transformers\Fractal\Baskets;

use GetCandy\Api\Core\Baskets\Models\BasketLine;
use GetCandy\Api\Http\Transformers\Fractal\BaseTransformer;
use GetCandy\Api\Http\Transformers\Fractal\Products\ProductVariantTransformer;

class BasketLineTransformer extends BaseTransformer
{
    protected $availableIncludes = [
        'variant',
    ];

    public function transform(BasketLine $line)
    {
        $data = array_merge($line->custom_attributes, [
            'id' => $line->encodedId(),
            'quantity' => $line->quantity,
            'line_total' => $line->total_cost,
            'unit_price' => $line->unit_cost,
            'tax' => $line->total_tax,
        ]);

        return $data;
    }

    protected function includeVariant(BasketLine $line)
    {
        return $this->item($line->variant, new ProductVariantTransformer);
    }
}
