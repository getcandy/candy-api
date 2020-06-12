<?php

namespace GetCandy\Api\Http\Transformers\Fractal\Discounts;

use GetCandy\Api\Core\Discounts\Models\DiscountRewardProduct;
use GetCandy\Api\Http\Transformers\Fractal\BaseTransformer;
use GetCandy\Api\Http\Transformers\Fractal\Products\ProductTransformer;

class DiscountRewardProductTransformer extends BaseTransformer
{
    /**
     * Resources that can be included if requested.
     *
     * @var array
     */
    protected $availableIncludes = ['product'];

    public function transform(DiscountRewardProduct $reward)
    {
        return [
            'id' => $reward->encodedId(),
            'value' => $reward->value,
        ];
    }

    public function includeProduct($reward)
    {
        return $this->item($reward->product, new ProductTransformer);
    }
}
