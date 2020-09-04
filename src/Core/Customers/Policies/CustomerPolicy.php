<?php

namespace GetCandy\Api\Core\Customers\Policies;

use GetCandy\Api\Core\Customers\Models\Customer;
use Illuminate\Foundation\Auth\User;

class CustomerPolicy
{
    /**
     * Determine if the user can create an address.
     *
     * @param  \App\User  $user
     * @return bool
     */
    public function create(?User $user)
    {
        return $user->can('create-customer');
    }

    public function update(?User $user, Customer $customer)
    {
        return $user->can('manage-addresses') || $user->customer_id == $customer->id;
    }

    public function view(?User $user, Customer $customer)
    {
        return $this->update($user, $customer);
    }

    public function delete(?User $user, Customer $customer)
    {
        return $this->update($user, $customer);
    }
}
