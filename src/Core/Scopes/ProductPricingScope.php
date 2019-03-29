<?php

namespace GetCandy\Api\Core\Scopes;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

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
        $isHub = $this->api->isHubRequest();
        if (! $this->user || ! $this->hasHubRoles || ($this->hasHubRoles && ! $isHub)) {
            $builder->whereHas('group', function ($query) {
                $query->whereIn('id', $this->groups);
            });
        }
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

    }
}
