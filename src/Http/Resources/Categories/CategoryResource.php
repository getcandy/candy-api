<?php

namespace GetCandy\Api\Http\Resources\Categories;

use GetCandy\Api\Http\Resources\AbstractResource;
use GetCandy\Api\Http\Resources\Assets\AssetCollection;
use GetCandy\Api\Http\Resources\Layouts\LayoutResource;
use GetCandy\Api\Http\Resources\Routes\RouteCollection;
use GetCandy\Api\Http\Resources\Channels\ChannelCollection;
use GetCandy\Api\Http\Resources\Products\ProductCollection;
use GetCandy\Api\Http\Resources\Attributes\AttributeCollection;
use GetCandy\Api\Http\Resources\Customers\CustomerGroupCollection;
use GetCandy\Api\Http\Resources\Assets\AssetResource;

class CategoryResource extends AbstractResource
{
    public function payload()
    {
        return [
            'id' => $this->encodedId(),
            'sort' => $this->sort,
            'products_count' => $this->when(! is_null($this->products_count), $this->products_count),
            'children_count' => $this->when(! is_null($this->children_count), $this->children_count),
        ];
    }

    public function includes()
    {
        return [
            'children' => new CategoryCollection($this->whenLoaded('children'), $this->only),
            'channels' => new ChannelCollection($this->whenLoaded('channels')),
            'ancestors' => new CategoryCollection($this->whenLoaded('ancestors')),
            'routes' => new RouteCollection($this->whenLoaded('routes')),
            'layout' => ['data' => new LayoutResource($this->whenLoaded('layout'))],
            'assets' => new AssetCollection($this->whenLoaded('assets')),
            'primary_asset' => ['data' => new AssetResource($this->whenLoaded('primaryAsset'))],
            'attributes' => new AttributeCollection($this->whenLoaded('attributes')),
            'routes' => new RouteCollection($this->whenLoaded('routes')),
            'customer_groups' => new CustomerGroupCollection($this->whenLoaded('customerGroups')),
            'products' => new ProductCollection($this->whenLoaded('products')),
        ];
    }
}
