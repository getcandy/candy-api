<?php

namespace GetCandy\Api\Http\Transformers\Fractal\Discounts;

use GetCandy\Api\Core\Discounts\Models\DiscountReward;
use GetCandy\Api\Http\Transformers\Fractal\BaseTransformer;

class DiscountRewardTransformer extends BaseTransformer
{
    protected $availableIncludes = ['products'];

    public function transform(DiscountReward $reward)
    {
        return [
            'id' => $reward->encodedId(),
            'type' => $reward->type,
            'value' => $reward->value,
        ];
    }

    public function includeProducts($reward)
    {
        return $this->collection($reward->products, new DiscountRewardProductTransformer);
    }
}
