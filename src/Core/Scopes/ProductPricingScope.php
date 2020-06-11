<?php

namespace GetCandy\Api\Core\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class ProductPricingScope extends AbstractScope
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
            $builder->whereHas('group', function ($query) {
                $query->whereIn('id', $this->getGroups());
            });
        });
    }

    /**
     * Extend the query builder with the needed functions.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $builder
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function extend(Builder $builder)
    {
        $builder->macro('withoutPricingScope', function (Builder $builder) {
            return $builder->withoutGlobalScope($this);
        });

        return $builder;
    }
}
