<?php

namespace GetCandy\Api\Http\Resources\Products;

use GetCandy\Api\Http\Resources\AbstractResource;

class ProductRecommendationResource extends AbstractResource
{
    public function payload()
    {
        return [];
    }

    public function includes()
    {
        return [
            'product' => ['data' => new ProductResource($this->whenLoaded('product'))],
        ];
    }
}
