<?php

namespace GetCandy\Api\Http\Transformers\Fractal\Categories;

use GetCandy\Api\Core\Categories\Models\Category;
use GetCandy\Api\Http\Transformers\Fractal\BaseTransformer;

class CategoryFancytreeTransformer extends BaseTransformer
{
    protected $defaultIncludes = [];

    public function transform(Category $category)
    {
        $data = [
            'id' => $category->encodedId(),
            'key' => $category->encodedId(),
            'attribute_data' => $category->attribute_data,
            'hasChildren' => $category->hasChildren(),
            'lazy' => $category->hasChildren(),
            'productCount' => $category->getProductCount(),
        ];

        return $data;
    }
}
