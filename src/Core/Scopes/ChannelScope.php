<?php

namespace GetCandy\Api\Core\Scopes;

use Carbon\Carbon;
use GetCandy\Api\Core\Channels\Interfaces\ChannelFactoryInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

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
        $this->resolve(function () use ($builder, $channel) {
            $builder->whereHas('channels', function ($query) use ($channel) {
                $query->whereHandle($channel->current())
                    ->whereDate('published_at', '<=', Carbon::now());
            });
        });
    }

    /**
     * Extend the query builder with the needed functions.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $builder
     * @return void
     */
    public function extend(Builder $builder)
    {
        $builder->macro('withoutChannelScope', function (Builder $builder) {
            return $builder->withoutGlobalScope($this);
        });
    }
}
