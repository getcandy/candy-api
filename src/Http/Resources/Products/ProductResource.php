<?php

namespace GetCandy\Api\Http\Resources\Products;

use GetCandy\Api\Http\Resources\AbstractResource;
use GetCandy\Api\Http\Resources\Assets\AssetCollection;
use GetCandy\Api\Http\Resources\Categories\CategoryCollection;
use GetCandy\Api\Http\Resources\Routes\RouteCollection;

class ProductResource extends AbstractResource
{
    public function payload()
    {
        return [
            'id' => $this->encodedId(),
        ];
    }

    public function includes()
    {
        return [
            'categories' => new CategoryCollection($this->whenLoaded('categories'), $this->only),
            'variants' => new ProductVariantCollection($this->whenLoaded('variants'), $this->only),
            'associations' => new ProductCollection($this->whenLoaded('associations'), $this->only),
            'assets' => new AssetCollection($this->whenLoaded('assets')),
            'routes' => new RouteCollection($this->whenLoaded('routes')),
        ];
    }
}