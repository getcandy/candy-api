<?php

namespace GetCandy\Api\Core\Addresses\Resources;

use GetCandy\Api\Http\Resources\AbstractResource;
use GetCandy\Api\Http\Resources\Countries\CountryResource;

class AddressResource extends AbstractResource
{
    public function payload()
    {
        return [
            'id' => $this->encoded_id,
            'salutation' => $this->salutation,
            'firstname' => $this->firstname,
            'lastname' => $this->lastname,
            'email' => $this->email,
            'phone' => $this->phone,
            'company_name' => $this->company_name,
            'address' => $this->address,
            'address_two' => $this->address_two,
            'address_three' => $this->address_three,
            'city' => $this->city,
            'state' => $this->state,
            'postal_code' => $this->postal_code,
            'billing' => (bool) $this->billing,
            'shipping' => (bool) $this->shipping,
            'default' => (bool) $this->default,
            'delivery_instructions' => $this->delivery_instructions,
            'last_used_at' => $this->last_used_at,
            'meta' => $this->meta,
        ];
    }

    public function includes()
    {
        return [
            'country' => $this->include('country', CountryResource::class),
        ];
    }
}
