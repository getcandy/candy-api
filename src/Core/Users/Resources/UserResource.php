<?php

namespace GetCandy\Api\Core\Users\Resources;

use GetCandy\Api\Http\Resources\AbstractResource;
use GetCandy\Api\Http\Resources\Acl\RoleCollection;
use GetCandy\Api\Http\Resources\Orders\OrderResource;
use GetCandy\Api\Http\Resources\Orders\OrderCollection;
use GetCandy\Api\Http\Resources\Baskets\BasketCollection;
use GetCandy\Api\Core\Customers\Resources\CustomerResource;
use GetCandy\Api\Core\Addresses\Resources\AddressCollection;
use GetCandy\Api\Http\Resources\Baskets\SavedBasketCollection;
use GetCandy\Api\Core\Customers\Resources\CustomerGroupCollection;
use GetCandy\Api\Core\ReusablePayments\Resources\ReusablePaymentCollection;

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
            'baskets' => new BasketCollection($this->whenLoaded('baskets')),
            'saved_baskets' => new SavedBasketCollection($this->whenLoaded('savedBaskets')),
            'roles' => new RoleCollection($this->whenLoaded('roles')),
            'reusable_payments' => new ReusablePaymentCollection($this->whenLoaded('reusablePayments')),
            'groups' => new CustomerGroupCollection($this->whenLoaded('groups')),
            'orders' => new OrderCollection($this->whenLoaded('orders')),
            'addresses' => new AddressCollection($this->whenLoaded('addresses')),
        ];
    }
}
