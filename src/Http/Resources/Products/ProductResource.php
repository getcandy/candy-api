<?php

namespace GetCandy\Api\Http\Resources\Products;

use GetCandy\Api\Http\Resources\AbstractResource;
use GetCandy\Api\Http\Resources\Assets\AssetResource;
use GetCandy\Api\Http\Resources\Assets\AssetCollection;
use GetCandy\Api\Http\Resources\Routes\RouteCollection;
use GetCandy\Api\Http\Resources\Channels\ChannelCollection;
use GetCandy\Api\Http\Resources\Categories\CategoryCollection;
use GetCandy\Api\Http\Resources\Attributes\AttributeCollection;
use GetCandy\Api\Http\Resources\Collections\CollectionCollection;
use GetCandy\Api\Http\Resources\Customers\CustomerGroupCollection;
use GetCandy\Api\Http\Resources\Discounts\DiscountModelCollection;

class ProductResource extends AbstractResource
{
    public function payload()
    {
        return [
            'id' => $this->encoded_id,
            'option_data' => $this->parseOptionData($this->option_data),
            'deleted_at' => $this->deleted_at,
        ];
    }

    public function optional()
    {
        return [

        ];
    }

    public function includes()
    {
        return [
            'attributes' => new AttributeCollection($this->whenLoaded('attributes')),
            'assets' => new AssetCollection($this->whenLoaded('assets')),
            'family' => $this->include('family', ProductFamilyResource::class),
            'routes' => new RouteCollection($this->whenLoaded('routes')),
            'channels' => new ChannelCollection($this->whenLoaded('channels'), $this->only),
            'first_variant' => $this->include('firstVariant', ProductVariantResource::class),
            'primary_asset' => $this->include('primaryAsset', AssetResource::class),
            'categories' => new CategoryCollection($this->whenLoaded('categories'), $this->only),
            'variants' => new ProductVariantCollection($this->whenLoaded('variants'), $this->only),
            'discounts' => new DiscountModelCollection($this->whenLoaded('discounts'), $this->only),
            'collections' => new CollectionCollection($this->whenLoaded('collections'), $this->only),
            'associations' => new ProductAssociationCollection($this->whenLoaded('associations'), $this->only),
            'customer_groups' => new CustomerGroupCollection($this->whenLoaded('customerGroups')),
        ];
    }

    protected function parseOptionData($data)
    {
        $data = $this->sortOptions($data);
        foreach ($data as $optionKey => $option) {
            $sorted = $this->sortOptions($option['options']);
            $data[$optionKey]['options'] = $sorted;
        }

        return $data;
    }

    protected function sortOptions($options)
    {
        $options = $options ?? [];

        uasort($options, function ($a, $b) {
            return $a['position'] < $b['position'] ? -1 : 1;
        });

        return $options;
    }
}
