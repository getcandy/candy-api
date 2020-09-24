<?php

namespace GetCandy\Api\Core\Traits;

use Auth;
use Carbon\Carbon;
use GetCandy;
use GetCandy\Api\Core\Channels\Actions\FetchDefaultChannel;
use GetCandy\Api\Core\Channels\Models\Channel;
use GetCandy\Api\Core\Scopes\ChannelScope;

trait HasChannels
{
    /**
     * Boot up the trait.
     */
    public static function bootHasChannels()
    {
        static::addGlobalScope(new ChannelScope);
    }

    public function scopeChannel($query, $channel = null)
    {
        $roles = GetCandy::roles()->getHubAccessRoles();
        $user = Auth::user();

        if (! $channel && ($user && $user->hasAnyRole($roles) && GetCandy::isHubRequest())) {
            return $query;
        }

        // // If no channel is set, we need to get the default one.
        if (! $channel) {
            $channel = FetchDefaultChannel::run()->handle;
        }

        // dump($channel, $this);
        return $query->whereHas('channels', function ($query) use ($channel) {
            $query->whereHandle($channel)
                ->whereNotNull('published_at')
                ->whereDate('published_at', '<=', Carbon::now());
        });
    }

    protected function getCustomerGroups()
    {
        // If there is a user, get their groups.
        if ($user = app('auth')->user()) {
            return $user->groups->pluck('id')->toArray();
        } else {
            $defaultGroup = FetchDefaultCustomerGroup::run();

            return [$defaultGroup->id];
        }
    }

    /**
     * Get the attributes associated to the product.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function channels()
    {
        return $this->belongsToMany(Channel::class)->withPivot('published_at');
    }
}
