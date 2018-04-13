<?php

namespace GetCandy\Api\Http\Transformers\Fractal\Products;

use GetCandy\Api\Http\Transformers\Fractal\BaseTransformer;
use GetCandy\Api\Http\Transformers\Fractal\Customers\CustomerGroupTransformer;
use GetCandy\Api\Products\Models\ProductPricingTier;

class ProductPricingTierTransformer extends BaseTransformer
{
    protected $availableIncludes = [
        'group',
    ];

    public function transform(ProductPricingTier $model)
    {
        return [
            'id'          => $model->encodedId(),
            'lower_limit' => $model->lower_limit,
            'price'       => $model->price,
        ];
    }

    public function includeGroup(ProductPricingTier $price)
    {
        return $this->item($price->group, new CustomerGroupTransformer());
    }
}
