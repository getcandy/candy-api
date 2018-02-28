<?php
namespace GetCandy\Api\Http\Transformers\Fractal\Baskets;

use Carbon\Carbon;
use GetCandy\Api\Baskets\Models\BasketLine;
use GetCandy\Api\Http\Transformers\Fractal\BaseTransformer;
use GetCandy\Api\Http\Transformers\Fractal\Products\ProductVariantTransformer;

class BasketLineTransformer extends BaseTransformer
{
    protected $availableIncludes = [
        'variant'
    ];

    public function transform(BasketLine $line)
    {
        $data = [
            'id' => $line->encodedId(),
            'quantity' => $line->quantity,
            'total' => $line->current_total,
            'total_when_added' => $line->total,
            'original_price' => $line->total / $line->quantity
        ];
        return $data;
    }

    protected function includeVariant(BasketLine $line)
    {
        return $this->item($line->variant, new ProductVariantTransformer);
    }
}
