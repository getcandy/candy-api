<?php

namespace GetCandy\Api\Core\Scopes;

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
            $model = $builder->getModel();
            $relation = $model->channels();

            $columnsToSelect = $this->filterColumns($builder, ["{$model->getTable()}.*"]);

            if ($columnsToSelect->count()) {
                $builder->addSelect($columnsToSelect->toArray());
            }

            $builder->join($relation->getTable(), function ($join) use ($relation, $model, $channel) {
                $join->on("{$model->getTable()}.id", '=', $relation->getExistenceCompareKey())
                ->where("{$relation->getTable()}.channel_id", $channel->getChannel()->id)
                ->whereDate("{$relation->getTable()}.published_at", '<=', now());
            })->groupBy($relation->getExistenceCompareKey());
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
