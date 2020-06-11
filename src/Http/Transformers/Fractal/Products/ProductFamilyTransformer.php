<?php

namespace GetCandy\Api\Http\Transformers\Fractal\Products;

use GetCandy\Api\Core\Products\Models\ProductFamily;
use GetCandy\Api\Core\Traits\IncludesAttributes;
use GetCandy\Api\Http\Transformers\Fractal\Attributes\AttributeTransformer;
use GetCandy\Api\Http\Transformers\Fractal\BaseTransformer;
use League\Fractal\ParamBag;

class ProductFamilyTransformer extends BaseTransformer
{
    use IncludesAttributes;

    /**
     * Resources that can be included if requested.
     *
     * @var array
     */
    protected $availableIncludes = [
        'products', 'attributes', 'attribute_groups',
    ];

    public function transform(ProductFamily $family)
    {
        return [
            'id' => $family->encodedId(),
            'attribute_data' => $family->attribute_data,
            'product_count' => $family->products()->count(),
            'attribute_count' => $family->attributes()->count(),
        ];
    }

    public function includeProducts(ProductFamily $family, ParamBag $params = null)
    {
        return $this->paginateInclude('products', $family, $params, new ProductTransformer);
    }

    public function includeAttributes(ProductFamily $family)
    {
        return $this->collection($family->attributes, new AttributeTransformer);
    }
}
