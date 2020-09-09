<?php

namespace GetCandy\Api\Http\Resources\Collections;

use GetCandy\Api\Core\Channels\Resources\ChannelCollection;
use GetCandy\Api\Core\Customers\Resources\CustomerGroupCollection;
use GetCandy\Api\Http\Resources\AbstractResource;
use GetCandy\Api\Http\Resources\Assets\AssetCollection;
use GetCandy\Api\Http\Resources\Attributes\AttributeCollection;
use GetCandy\Api\Http\Resources\Products\ProductCollection;
use GetCandy\Api\Http\Resources\Routes\RouteCollection;
use GetCandy\Api\Http\Resources\Versioning\VersionCollection;

class CollectionResource extends AbstractResource
{
    public function payload()
    {
        return [
            'id' => $this->encoded_id,
            'drafted_at' => $this->drafted_at,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }

    public function includes()
    {
        return [
            'routes' => new RouteCollection($this->whenLoaded('routes')),
            'draft' => ['data' => new self($this->whenLoaded('draft'))],
            'channels' => new ChannelCollection($this->whenLoaded('channels')),
            'published_parent' => ['data' => new self($this->whenLoaded('publishedParent'))],
            'assets' => new AssetCollection($this->whenLoaded('assets')),
            'attributes' => new AttributeCollection($this->whenLoaded('attributes')),
            'products' => new ProductCollection($this->whenLoaded('products')),
            'customer_groups' => new CustomerGroupCollection($this->whenLoaded('customerGroups')),
            'versions' => new VersionCollection($this->whenLoaded('versions'), $this->only),
        ];
    }
}
