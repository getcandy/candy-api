<?php

namespace GetCandy\Api\Http\Transformers\Fractal\Products;

use GetCandy\Api\Core\Products\Factories\ProductFactory;
use GetCandy\Api\Http\Transformers\Fractal\BaseTransformer;
use GetCandy\Api\Core\Products\Models\ProductRecommendation;

class ProductRecommendationTransformer extends BaseTransformer
{
    protected $availableIncludes = [
        'product',
    ];

    public function transform(ProductRecommendation $model)
    {
        return [
            'ranking' => $model->count,
        ];
    }

    public function includeProduct(ProductRecommendation $model)
    {
        $factory = app()->make(ProductFactory::class);

        return $this->item(
            $factory->init($model->product)->get(),
            new ProductTransformer
        );
    }
}
