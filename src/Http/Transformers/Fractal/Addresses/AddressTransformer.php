<?php

namespace GetCandy\Api\Http\Transformers\Fractal\Addresses;

use GetCandy\Api\Core\Addresses\Models\Address;
use GetCandy\Api\Http\Transformers\Fractal\BaseTransformer;

class AddressTransformer extends BaseTransformer
{
    public function transform(Address $address)
    {
        return [
            'id' => $address->encodedId(),
            'firstname' => $address->firstname,
            'lastname' => $address->lastname,
            'email' => $address->email,
            'address' => $address->address,
            'address_two' => $address->address_two,
            'address_three' => $address->address_three,
            'city' => $address->city,
            'state' => $address->state,
            'county' => $address->county,
            'country' => $address->country,
            'zip' => $address->zip,
            'billing' => (bool) $address->billing,
            'shipping' => (bool) $address->shipping,
            'default' => (bool) $address->default,
        ];
    }
}
