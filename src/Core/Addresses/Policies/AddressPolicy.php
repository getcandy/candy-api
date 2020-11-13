<?php

namespace GetCandy\Api\Core\Addresses\Policies;

use GetCandy;
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
        $userModel = GetCandy::getUserModel();

        return $user->can('manage-addresses') || ($address->addressable_type == $userModel && $user->id == $address->addressable_id);
    }

    public function view(?User $user, Address $address)
    {
        return $this->update($user, $address);
    }

    public function delete(?User $user, Address $address)
    {
        return $this->update($user, $address);
    }
}
