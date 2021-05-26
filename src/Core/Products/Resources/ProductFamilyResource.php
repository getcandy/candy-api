<?php

namespace GetCandy\Api\Core\Products\Resources;

use GetCandy\Api\Http\Resources\AbstractResource;
use GetCandy\Api\Http\Resources\Attributes\AttributeCollection;

class ProductFamilyResource extends AbstractResource
{
    public function payload()
    {
        return [
            'id' => $this->encoded_id,
            'name' => $this->name,
            'products_count' => (int) $this->products_count ?: 0,
            'attributes_count' => (int) $this->attributes_count ?: 0,
        ];
    }

    public function includes()
    {
        return [
            'attributes' => new AttributeCollection($this->whenLoaded('attributes')),
        ];
    }
}
