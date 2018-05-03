<?php

namespace GetCandy\Api\Http\Transformers\Fractal\Discounts;

use GetCandy\Api\Core\Discounts\Models\DiscountCriteriaSet;
use GetCandy\Api\Http\Transformers\Fractal\BaseTransformer;

class DiscountSetTransformer extends BaseTransformer
{
    protected $availableIncludes = [
        'items',
    ];

    public function transform(DiscountCriteriaSet $set)
    {
        return [
            'id' => $set->encodedId(),
            'scope' => $set->scope,
            'outcome' => (bool) $set->outcome,
        ];
    }

    public function includeItems(DiscountCriteriaSet $set)
    {
        return $this->collection($set->items, new DiscountItemTransformer);
    }
}
