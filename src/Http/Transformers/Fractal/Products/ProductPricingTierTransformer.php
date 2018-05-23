<?php

namespace GetCandy\Api\Http\Transformers\Fractal\Products;

use PriceCalculator;
use GetCandy\Api\Core\Products\Models\ProductPricingTier;
use GetCandy\Api\Http\Transformers\Fractal\BaseTransformer;
use GetCandy\Api\Http\Transformers\Fractal\Customers\CustomerGroupTransformer;

class ProductPricingTierTransformer extends BaseTransformer
{
    protected $availableIncludes = [
        'group',
    ];

    public function transform(ProductPricingTier $model)
    {
        $pricing = PriceCalculator::get($model->price, 'default');

        return [
            'id' => $model->encodedId(),
            'lower_limit' => $model->lower_limit,
            'price' => $pricing->amount,
            'tax' => $pricing->tax,
        ];
    }

    public function includeGroup(ProductPricingTier $price)
    {
        return $this->item($price->group, new CustomerGroupTransformer);
    }
}
