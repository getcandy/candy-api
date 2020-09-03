<?php

namespace GetCandy\Api\Http\Resources\Users;

use GetCandy\Api\Http\Resources\AbstractResource;
use GetCandy\Api\Http\Resources\Acl\RoleCollection;
use GetCandy\Api\Http\Resources\Orders\OrderResource;
use GetCandy\Api\Http\Resources\Orders\OrderCollection;
use GetCandy\Api\Core\Customers\Resources\CustomerResource;
use GetCandy\Api\Core\Addresses\Resources\AddressCollection;
use GetCandy\Api\Http\Resources\Customers\CustomerGroupCollection;

class UserResource extends AbstractResource
{
    public function payload()
    {
        return [
            'id' => $this->encoded_id,
            'email' => $this->email,
            'name' => $this->name,
        ];
    }

    public function includes()
    {
        return [
            'customer' => $this->include('customer', CustomerResource::class),
            'first_order' => $this->include('firstOrder', OrderResource::class),
            'roles' => new RoleCollection($this->whenLoaded('roles')),
            'groups' => new CustomerGroupCollection($this->whenLoaded('groups')),
            'orders' => new OrderCollection($this->whenLoaded('orders')),
            'addresses' => new AddressCollection($this->whenLoaded('addresses')),
        ];
    }
}
