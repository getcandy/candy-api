<?php

namespace GetCandy\Api\Http\Transformers\Fractal\Discounts;

use GetCandy\Api\Core\Discounts\Models\DiscountCriteriaItem;
use GetCandy\Api\Http\Transformers\Fractal\BaseTransformer;
use GetCandy\Api\Http\Transformers\Fractal\Customers\CustomerGroupTransformer;
use GetCandy\Api\Http\Transformers\Fractal\Products\ProductTransformer;
use GetCandy\Api\Http\Transformers\Fractal\Users\UserTransformer;

class DiscountItemTransformer extends BaseTransformer
{
    /**
     * Resources that can be included if requested.
     *
     * @var array
     */
    protected $availableIncludes = [
        'eligibles',
    ];

    public function transform(DiscountCriteriaItem $item)
    {
        return [
            'id' => $item->encodedId(),
            'type' => $item->type,
            'value' => $item->value,
        ];
    }

    public function includeEligibles(DiscountCriteriaItem $item)
    {
        if ($item->customerGroups->count()) {
            return $this->collection($item->customerGroups, new CustomerGroupTransformer);
        } elseif ($item->users->count()) {
            return $this->collection($item->users, new UserTransformer);
        } elseif ($item->products->count()) {
            return $this->collection($item->products, new ProductTransformer);
        }
        // return $this->respondWithCollection($item->eligibles, );
    }
}
