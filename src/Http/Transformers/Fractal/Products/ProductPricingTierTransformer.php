<?php

namespace GetCandy\Api\Http\Transformers\Fractal\Products;

use GetCandy\Api\Http\Transformers\Fractal\BaseTransformer;
use GetCandy\Api\Products\Models\ProductPricingTier;
use GetCandy\Api\Http\Transformers\Fractal\Customers\CustomerGroupTransformer;

class ProductPricingTierTransformer extends BaseTransformer
{
    protected $availableIncludes = [
        'group'
    ];

    public function transform(ProductPricingTier $model)
    {
        $pricing = PriceCalculator::get($model->price, 20);
        return [
            'id' => $model->encodedId(),
            'lower_limit' => $model->lower_limit,
            'price' => $pricing->amount
        ];
    }

    public function includeGroup(ProductPricingTier $price)
    {
        return $this->item($price->group, new CustomerGroupTransformer);
    }
}
