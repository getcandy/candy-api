<?php

namespace GetCandy\Api\Core\Scopes;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class CustomerGroupScope extends AbstractScope
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
        $this->resolve(function () use ($builder) {
            $builder->whereHas('customerGroups', function ($q) {
                $q->whereIn('customer_groups.id', $this->getGroups())->where('visible', '=', true);
            });
        });
    }

    /**
     * Extend the query builder with the needed functions.
     *
     * @param Builder $builder
     */
    public function extend(Builder $builder)
    {
        $builder->macro('forAnyCustomerGroup', function (Builder $builder) {
            return $builder->withoutGlobalScope($this);
        });

        return $builder;
    }
}
