<?php

namespace GetCandy\Api\Http\Resources\Users;

use GetCandy\Api\Http\Resources\AbstractResource;
use GetCandy\Api\Http\Resources\Orders\OrderResource;
use GetCandy\Api\Http\Resources\Acl\RoleCollection;
use GetCandy\Api\Http\Resources\Addresses\AddressCollection;

class UserResource extends AbstractResource
{
    public function payload()
    {
        return [
            'id' => $this->encoded_id,
            'email' => $this->email,
        ];
    }

    public function includes()
    {
        return [
            // 'details' => $this->include('details', UserDetailsResource::class),
            'details' => $this->include('details', UserDetailsResource::class),
            'first_order' => $this->include('firstOrder', OrderResource::class),
            'roles' => new RoleCollection($this->whenLoaded('roles')),
            'addresses' => new AddressCollection($this->whenLoaded('addresses')),
        ];
    }
}
