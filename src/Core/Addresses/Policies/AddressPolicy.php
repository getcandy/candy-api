<?php

namespace GetCandy\Api\Core\Addresses\Policies;

use GetCandy\Api\Core\Addresses\Models\Address;
use Illuminate\Foundation\Auth\User;

class AddressPolicy
{
    /**
     * Determine if the user can create an address.
     *
     * @param  \App\User  $user
     * @return bool
     */
    public function create(?User $user)
    {
        return $user->can('create-address');
    }

    public function update(?User $user, Address $address)
    {
        return $user->can('manage-addresses') || $user->id === $address->user_id;
    }

    public function delete(?User $user, Address $address)
    {
        return $this->update($user, $address);
    }
}
