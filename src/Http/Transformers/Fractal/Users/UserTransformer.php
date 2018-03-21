<?php

namespace GetCandy\Api\Http\Transformers\Fractal\Users;

use League\Fractal\TransformerAbstract;
use GetCandy\Api\Http\Transformers\Fractal\BaseTransformer;
use GetCandy\Api\Http\Transformers\Fractal\Orders\OrderTransformer;
use GetCandy\Api\Http\Transformers\Fractal\Addresses\AddressTransformer;
use GetCandy\Api\Http\Transformers\Fractal\Languages\LanguageTransformer;
use GetCandy\Api\Http\Transformers\Fractal\Customers\CustomerGroupTransformer;
use Illuminate\Database\Eloquent\Model;

class UserTransformer extends BaseTransformer
{
    protected $availableIncludes = [
        'store', 'addresses', 'groups', 'roles', 'orders', 'language'
    ];

    public function transform(Model $user)
    {
        return [
            'id' => $user->encodedId(),
            'title' => $user->title,
            'firstname' => $user->firstname,
            'lastname' => $user->lastname,
            'company_name' => $user->company_name,
            'contact_number' => $user->contact_number,
            'vat_no' => $user->vat_no,
            'email' => $user->email
        ];
    }

    public function includeLanguage(Model $user)
    {
        return $this->item($user->language, new LanguageTransformer);
    }

    public function includeAddresses(Model $user)
    {
        return $this->collection($user->addresses, new AddressTransformer);
    }

    public function includeGroups(Model $user)
    {
        return $this->collection($user->groups, new CustomerGroupTransformer);
    }

    public function includeRoles(Model $user)
    {
        return $this->collection($user->roles, new UserRoleTransformer);
    }

    public function includeOrders(Model $user)
    {
        return $this->collection($user->orders, new OrderTransformer);
    }
}
