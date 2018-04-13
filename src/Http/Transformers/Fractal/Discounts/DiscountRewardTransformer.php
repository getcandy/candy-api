<?php

namespace GetCandy\Api\Http\Transformers\Fractal\Discounts;

use GetCandy\Api\Discounts\Models\DiscountReward;
use GetCandy\Api\Http\Transformers\Fractal\BaseTransformer;

class DiscountRewardTransformer extends BaseTransformer
{
    public function transform(DiscountReward $reward)
    {
        return [
            'id'    => $reward->encodedId(),
            'type'  => $reward->type,
            'value' => $reward->value,
        ];
    }
}
