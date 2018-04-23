<?php

namespace GetCandy\Api\Core\Scopes;

use Auth;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;
use Illuminate\Database\Eloquent\Builder;

class CustomerGroupScope implements Scope
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
        $roles = app('api')->roles()->getHubAccessRoles();

        $groups = $this->getCustomerGroups();
        $user = app('auth')->user();

        if (! $user || ! $user->hasAnyRole($roles)) {
            $builder->whereHas('customerGroups', function ($q) use ($groups) {
                $q->whereIn('customer_groups.id', $groups)->where('visible', '=', true);
            });
        }
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
