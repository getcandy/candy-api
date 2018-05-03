<?php

namespace GetCandy\Api\Core\Traits;

use Carbon\Carbon;
use GetCandy\Api\Core\Channels\Models\Channel;

trait HasChannels
{
    public function scopeChannel($query, $channel = null)
    {
        $roles = app('api')->roles()->getHubAccessRoles();
        $groups = $this->getCustomerGroups();
        $user = app('auth')->user();
        $channels = app('api')->channels();

        if (! $channel && ($user && $user->hasAnyRole($roles))) {
            return $query;
        }

        // If no channel is set, we need to get the default one.
        if (! $channel) {
            $channel = $channels->getDefaultRecord()->handle;
        }

        return $query->whereHas('channels', function ($query) use ($channel) {
            $query->whereHandle($channel)->whereDate('published_at', '<=', Carbon::now());
        });
    }

    protected function getCustomerGroups()
    {
        // If there is a user, get their groups.
        if ($user = app('auth')->user()) {
            return $user->groups->pluck('id')->toArray();
        } else {
            return [app('api')->customerGroups()->getGuestId()];
        }
    }

    /**
     * Get the attributes associated to the product.
     * @return Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function channels()
    {
        return $this->belongsToMany(Channel::class)->withPivot('published_at');
    }
}
