<?php

namespace GetCandy\Api\Http\Resources\Products;

use GetCandy\Api\Core\Attributes\Resources\AttributeCollection;
use GetCandy\Api\Http\Resources\AbstractResource;

class ProductFamilyResource extends AbstractResource
{
    public function payload()
    {
        return [
            'id' => $this->encoded_id,
            'name' => $this->name,
        ];
    }

    public function includes()
    {
        return [
            'attributes' => new AttributeCollection($this->whenLoaded('attributes')),
        ];
    }
}
