<?php

namespace GetCandy\Api\Core\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

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
            $model = $builder->getModel();
            $relation = $model->customerGroups();
            $selects = [];

            $builder->join($relation->getTable(), function ($join) use ($relation, $model) {
                $join->on("{$model->getTable()}.id", '=', $relation->getExistenceCompareKey())
                ->whereIn("{$relation->getTable()}.customer_group_id", $this->getGroups())
                ->where("{$relation->getTable()}.visible", '=', true)
                ->groupBy($relation->getExistenceCompareKey());
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
        $builder->macro('forAnyCustomerGroup', function (Builder $builder) {
            return $builder->withoutGlobalScope($this);
        });

        return $builder;
    }
}
