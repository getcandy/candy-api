<?php

namespace GetCandy\Api\Http\Resources\Categories;

use GetCandy\Api\Core\Channels\Resources\ChannelCollection;
use GetCandy\Api\Core\Customers\Resources\CustomerGroupCollection;
use GetCandy\Api\Http\Resources\AbstractResource;
use GetCandy\Api\Http\Resources\Assets\AssetCollection;
use GetCandy\Api\Http\Resources\Assets\AssetResource;
use GetCandy\Api\Http\Resources\Attributes\AttributeCollection;
use GetCandy\Api\Http\Resources\Layouts\LayoutResource;
use GetCandy\Api\Http\Resources\Products\ProductCollection;
use GetCandy\Api\Http\Resources\Routes\RouteCollection;
use GetCandy\Api\Http\Resources\Versioning\VersionCollection;

class CategoryResource extends AbstractResource
{
    public function payload()
    {
        return [
            'id' => $this->encodedId(),
            'sort' => $this->sort,
            'drafted_at' => $this->drafted_at,
            'products_count' => $this->products()->count(),
            'children_count' => $this->children()->count(),
            'depth' => $this->depth,
            'has_draft' => $this->draft()->exists(),
            'left_pos' => $this->_lft,
            'right_pos' => $this->_rgt,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }

    public function includes()
    {
        return [
            'children' => new CategoryCollection($this->whenLoaded('children'), $this->only),
            'draft' => ['data' => new self($this->whenLoaded('draft'))],
            'published_parent' => ['data' => new self($this->whenLoaded('publishedParent'))],
            'channels' => new ChannelCollection($this->whenLoaded('channels')),
            'ancestors' => new CategoryCollection($this->whenLoaded('ancestors')),
            'routes' => new RouteCollection($this->whenLoaded('routes')),
            'layout' => ['data' => new LayoutResource($this->whenLoaded('layout'))],
            'assets' => new AssetCollection($this->whenLoaded('assets')),
            'primary_asset' => ['data' => new AssetResource($this->whenLoaded('primaryAsset'))],
            'attributes' => new AttributeCollection($this->whenLoaded('attributes')),
            'customer_groups' => new CustomerGroupCollection($this->whenLoaded('customerGroups')),
            'products' => new ProductCollection($this->whenLoaded('products')),
            'versions' => new VersionCollection($this->whenLoaded('versions'), $this->only),
        ];
    }
}
