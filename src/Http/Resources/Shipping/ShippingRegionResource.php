<?php

namespace GetCandy\Api\Http\Resources\Shipping;

use GetCandy\Api\Http\Resources\AbstractResource;

class ShippingRegionResource extends AbstractResource
{
    public function payload()
    {
        return [
            'id' => $this->encoded_id,
            'region' => $this->region,
            'address_field' => $this->address_field,
        ];
    }

    public function includes()
    {
        return [

        ];
    }
}
