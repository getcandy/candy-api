<?php

namespace GetCandy\Api\Http\Transformers\Fractal\Users;

use GetCandy\Api\Auth\Models\User;
use League\Fractal\TransformerAbstract;
use GetCandy\Api\Http\Transformers\Fractal\BaseTransformer;
use GetCandy\Api\Http\Transformers\Fractal\Orders\OrderTransformer;
use GetCandy\Api\Http\Transformers\Fractal\Addresses\AddressTransformer;
use GetCandy\Api\Http\Transformers\Fractal\Languages\LanguageTransformer;
use GetCandy\Api\Http\Transformers\Fractal\Customers\CustomerGroupTransformer;

class UserTransformer extends BaseTransformer
{
    protected $defaultIncludes = [
        'language'
    ];

    protected $availableIncludes = [
        'store', 'addresses', 'groups', 'roles', 'orders', 'details'
    ];

    public function transform($user)
    {
        return [
            'id' => $user->encodedId(),
            'email' => $user->email,
            'phone_number'=> $user->phone_number,
            'avatar'=> $user->avatar
        ];
    }

    public function includeLanguage($user)
    {
        if (!$user->language) {
            return $this->null();
        }
        return $this->item($user->language, new LanguageTransformer);
    }

    public function includeAddresses($user)
    {
        return $this->collection($user->addresses, new AddressTransformer);
    }

    public function includeGroups($user)
    {
        return $this->collection($user->groups, new CustomerGroupTransformer);
    }

    public function includeRoles($user)
    {
        return $this->collection($user->roles, new UserRoleTransformer);
    }

    public function includeOrders($user)
    {
        return $this->collection($user->orders, new OrderTransformer);
    }

    public function includeDetails($user)
    {
        if (!$user->details) {
            return null;
        }
        return $this->item($user->details, new UserDetailsTransformer);
    }
}
