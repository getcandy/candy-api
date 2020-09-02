<?php

namespace GetCandy\Api\Core\Channels\Policies;

use GetCandy\Api\Core\Channels\Models\Channel;
use Illuminate\Foundation\Auth\User;

class ChannelPolicy
{
    /**
     * Determine if the user can create an address.
     *
     * @param  \App\User  $user
     * @return bool
     */
    public function create(?User $user)
    {
        return $user->can('create-channel');
    }

    public function update(?User $user, Channel $channel)
    {
        return $user->can('manage-channels');
    }

    public function view(?User $user, Channel $channel)
    {
        return $this->update($user, $channel);
    }

    public function delete(?User $user, Channel $channel)
    {
        return $this->update($user, $channel);
    }
}
