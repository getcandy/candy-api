<?php

namespace GetCandy\Api\Http\Transformers\Fractal\Products;

use GetCandy\Api\Traits\IncludesAttributes;
use GetCandy\Api\Products\Models\ProductFamily;
use GetCandy\Api\Http\Transformers\Fractal\BaseTransformer;
use GetCandy\Api\Http\Transformers\Fractal\Attributes\AttributeTransformer;

class ProductFamilyTransformer extends BaseTransformer
{
    use IncludesAttributes;

    protected $availableIncludes = [
        'products', 'attributes', 'attribute_groups',
    ];

    public function transform(ProductFamily $family)
    {
        return [
            'id' => $family->encodedId(),
            'name' => $family->name,
        ];
    }

    public function includeProducts(ProductFamily $family)
    {
        return $this->collection($family->products, new ProductTransformer);
    }

    public function includeAttributes(ProductFamily $family)
    {
        return $this->collection($family->attributes, new AttributeTransformer);
    }
}
