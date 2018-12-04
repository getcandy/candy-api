<?php

namespace GetCandy\Api\Http\Resources\Products;

use GetCandy\Api\Http\Resources\AbstractResource;
use GetCandy\Api\Http\Resources\Assets\AssetCollection;
use GetCandy\Api\Http\Resources\Routes\RouteCollection;
use GetCandy\Api\Http\Resources\Discounts\DiscountCollection;
use GetCandy\Api\Http\Resources\Categories\CategoryCollection;
use GetCandy\Api\Http\Resources\Discounts\DiscountModelCollection;

class ProductResource extends AbstractResource
{
    public function payload()
    {
        return [
            'id' => $this->encoded_id,
        ];
    }

    public function includes()
    {
        return [
            'assets' => new AssetCollection($this->whenLoaded('assets')),
            'routes' => new RouteCollection($this->whenLoaded('routes')),
            'categories' => new CategoryCollection($this->whenLoaded('categories'), $this->only),
            'variants' => new ProductVariantCollection($this->whenLoaded('variants'), $this->only),
            'associations' => new ProductAssociationCollection($this->whenLoaded('associations'), $this->only),
            'discounts' => new DiscountModelCollection($this->whenLoaded('discounts'), $this->only),
            'first_variant' => $this->include('firstVariant', ProductVariantResource::class),
        ];
    }
}