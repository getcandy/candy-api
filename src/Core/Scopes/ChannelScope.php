<?php

namespace GetCandy\Api\Core\Scopes;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use GetCandy\Api\Core\Channels\Interfaces\ChannelFactoryInterface;

class ChannelScope extends AbstractScope
{
    /**
     * Apply the scope to a given Eloquent query builder.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $builder
     * @param  \Illuminate\Database\Eloquent\Model  $model
     * @return void
     */
    public function apply(Builder $builder, Model $model)
    {
        $channel = app()->getInstance()->make(ChannelFactoryInterface::class);
        $isHub = $this->api->isHubRequest();

        if (! $channel && ($this->user && $this->hasHubRoles && $isHub)) {
            return $builder;
        }

        $builder->whereHas('channels', function ($query) use ($channel) {
            $query->whereHandle($channel->current())
                ->whereDate('published_at', '<=', Carbon::now());
        });
    }

    /**
     * Remove the scope from the given Eloquent query builder.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $builder
     * @param  \Illuminate\Database\Eloquent\Model  $model
     * @return void
     */
    public function remove(Builder $builder, Model $model)
    {
        dd('hit');
    }
}
