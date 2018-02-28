<?php
namespace GetCandy\Api\Http\Transformers\Fractal\Discounts;

use Carbon\Carbon;
use GetCandy\Api\Discounts\Models\Discount;
use GetCandy\Api\Discounts\Models\DiscountReward;
use GetCandy\Api\Discounts\Models\DiscountCriteriaItem;
use GetCandy\Api\Http\Transformers\Fractal\BaseTransformer;

class DiscountRewardTransformer extends BaseTransformer
{
    public function transform(DiscountReward $reward)
    {
        return [
            'id' => $reward->encodedId(),
            'type' => $reward->type,
            'value' => $reward->value
        ];
    }
}
