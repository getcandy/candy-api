<?php

namespace GetCandy\Api\Http\Resources\Products;

use GetCandy\Api\Http\Resources\AbstractResource;

class ProductAssociationResource extends AbstractResource
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
            'association' => ['data' => new ProductResource($this->whenLoaded('association'), $this->only)],
        ];
    }
}
