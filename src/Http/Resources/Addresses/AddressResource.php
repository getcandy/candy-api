<?php

namespace GetCandy\Api\Http\Resources\Addresses;

use GetCandy\Api\Http\Resources\AbstractResource;

class AddressResource extends AbstractResource
{
    public function payload()
    {
        return [
            'id' => $this->encoded_id,
            'firstname' => $this->firstname,
            'lastname' => $this->lastname,
            'email' => $this->email,
            'address' => $this->address,
            'address_two' => $this->address_two,
            'address_three' => $this->address_three,
            'city' => $this->city,
            'state' => $this->state,
            'county' => $this->county,
            'country' => $this->country,
            'zip' => $this->zip,
            'billing' => (bool) $this->billing,
            'shipping' => (bool) $this->shipping,
            'default' => (bool) $this->default,
        ];
    }
}
