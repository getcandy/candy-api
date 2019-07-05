<?php

namespace GetCandy\Api\Http\Resources\Collections;

use GetCandy\Api\Http\Resources\AbstractResource;
use GetCandy\Api\Http\Resources\Assets\AssetCollection;
use GetCandy\Api\Http\Resources\Layouts\LayoutResource;
use GetCandy\Api\Http\Resources\Routes\RouteCollection;
use GetCandy\Api\Http\Resources\Channels\ChannelCollection;
use GetCandy\Api\Http\Resources\Products\ProductCollection;
use GetCandy\Api\Http\Resources\Attributes\AttributeCollection;
use GetCandy\Api\Http\Resources\Customers\CustomerGroupCollection;

class CollectionResource extends AbstractResource
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
            'routes' => new RouteCollection($this->whenLoaded('routes')),
            'layout' => ['data' => new LayoutResource($this->whenLoaded('layout'))],
            'channels' => new ChannelCollection($this->whenLoaded('channels')),
            'assets' => new AssetCollection($this->whenLoaded('assets')),
            'attributes' => new AttributeCollection($this->whenLoaded('attributes')),
            'routes' => new RouteCollection($this->whenLoaded('routes')),
            'products' => new ProductCollection($this->whenLoaded('products')),
            'customer_groups' => new CustomerGroupCollection($this->whenLoaded('customerGroups')),
        ];
    }
}
