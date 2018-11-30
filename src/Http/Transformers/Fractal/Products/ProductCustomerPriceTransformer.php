<?php

namespace GetCandy\Api\Http\Transformers\Fractal\Products;

use GetCandy\Api\Core\Products\Models\ProductCustomerPrice;
use GetCandy\Api\Http\Transformers\Fractal\BaseTransformer;
use GetCandy\Api\Http\Transformers\Fractal\Taxes\TaxTransformer;
use GetCandy\Api\Http\Transformers\Fractal\Customers\CustomerGroupTransformer;

class ProductCustomerPriceTransformer extends BaseTransformer
{
    protected $availableIncludes = [
        'tax', 'group',
    ];

    public function transform(ProductCustomerPrice $model)
    {
        return [
            'id' => $model->encodedId(),
            'price' => $model->price * 100,
            'compare_at_price' => $model->compare_at_price,
        ];
    }

    public function includeTax(ProductCustomerPrice $price)
    {
        if (! $price->tax) {
            return $this->null();
        }

        return $this->item($price->tax, new TaxTransformer);
    }

    public function includeGroup(ProductCustomerPrice $price)
    {
        return $this->item($price->group, new CustomerGroupTransformer);
    }
}
