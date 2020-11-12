<?php

namespace GetCandy\Api\Http\Resources\Products;

use GetCandy\Api\Http\Resources\AbstractResource;
use GetCandy\Api\Http\Resources\Taxes\TaxResource;
use GetCandy\Api\Core\Customers\Resources\CustomerGroupResource;

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
            'tax' => ['data' => new TaxResource($this->whenLoaded('tax'))],
            'group' => ['data' => new CustomerGroupResource($this->whenLoaded('group'))],
        ];
    }
}
