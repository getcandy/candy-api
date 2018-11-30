<?php

namespace GetCandy\Api\Http\Transformers\Fractal\Shipping;

use GetCandy\Api\Core\Shipping\Models\ShippingZone;
use GetCandy\Api\Http\Transformers\Fractal\BaseTransformer;
use GetCandy\Api\Http\Transformers\Fractal\Countries\CountryTransformer;

class ShippingZoneTransformer extends BaseTransformer
{
    protected $availableIncludes = [
        'countries',
    ];

    public function transform(ShippingZone $zone)
    {
        return [
            'id' => $zone->encodedId(),
            'name' => $zone->name,
        ];
    }

    public function includeCountries(ShippingZone $zone)
    {
        return $this->collection($zone->countries, new CountryTransformer);
    }
}
