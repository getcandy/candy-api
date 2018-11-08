<?php

namespace GetCandy\Api\Http\Resources\Products;

use GetCandy\Api\Http\Resources\AbstractResource;

class ProductResource extends AbstractResource
{
    public function payload()
    {
        return [
            'id' => $this->encodedId(),
        ];
    }
}