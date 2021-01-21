<?php

namespace GetCandy\Api\Http\Resources\Products;

use GetCandy\Api\Core\Customers\Resources\CustomerGroupResource;
use GetCandy\Api\Http\Resources\AbstractResource;

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
