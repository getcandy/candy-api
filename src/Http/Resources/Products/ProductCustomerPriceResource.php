<?php

namespace GetCandy\Api\Http\Resources\Products;

use GetCandy\Api\Http\Resources\AbstractResource;
use GetCandy\Api\Http\Resources\Taxes\TaxResource;
use GetCandy\Api\Http\Resources\Customers\CustomerGroupResource;

class ProductCustomerPriceResource extends AbstractResource
{
    public function payload()
    {
        return [
            'id' => $this->encoded_id,
            'price' => $this->price,
        ];
    }

    public function includes()
    {
        return [
            'tax' => new TaxResource($this->whenLoaded('tax')),
            'group' => new CustomerGroupResource($this->whenLoaded('group')),
        ];
    }
}
