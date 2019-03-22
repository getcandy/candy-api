<?php

namespace GetCandy\Api\Http\Resources\Products;

use GetCandy\Api\Http\Resources\AbstractResource;
use GetCandy\Api\Http\Resources\Customers\CustomerGroupResource;

class ProductTierResource extends AbstractResource
{
    public function payload()
    {
        return [
            'id' => $this->encodedId(),
            'lower_limit' => $this->lower_limit,
            'price' => $this->total_cost,
            'tax' => $this->total_tax,
        ];
    }

    public function includes()
    {
        return [
            'group' => new CustomerGroupResource($this->whenLoaded('group')),
        ];
    }
}
