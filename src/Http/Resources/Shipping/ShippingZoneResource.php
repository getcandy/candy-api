<?php

namespace GetCandy\Api\Http\Resources\Shipping;

use GetCandy\Api\Core\Countries\Resources\CountryCollection;
use GetCandy\Api\Http\Resources\AbstractResource;

class ShippingZoneResource extends AbstractResource
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
            'regions' => new ShippingRegionCollection($this->whenLoaded('regions')),
            'countries' => new CountryCollection($this->whenLoaded('countries')),
        ];
    }
}
