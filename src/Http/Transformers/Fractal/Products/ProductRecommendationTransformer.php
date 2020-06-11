<?php

namespace GetCandy\Api\Http\Transformers\Fractal\Products;

use GetCandy\Api\Core\Products\Factories\ProductFactory;
use GetCandy\Api\Core\Products\Models\ProductRecommendation;
use GetCandy\Api\Http\Transformers\Fractal\BaseTransformer;

class ProductRecommendationTransformer extends BaseTransformer
{
    /**
     * Resources that can be included if requested.
     *
     * @var array
     */
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
