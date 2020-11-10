<?php

namespace GetCandy\Api\Core\Scopes;

use GetCandy;
use GetCandy\Api\Core\Customers\Actions\FetchDefaultCustomerGroup;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Scope;

abstract class AbstractScope implements Scope
{
    /**
     * The customer groups.
     *
     * @var array
     */
    protected $groups;

    /**
     * The hub roles.
     *
     * @var array
     */
    protected $roles;

    /**
     * The current user.
     *
     * @var \Illuminate\Foundation\Auth\User
     */
    protected $user;

    /**
     * Whether the user has Hub roles.
     *
     * @var bool
     */
    protected $hasHubRoles = false;

    public function __construct()
    {
        $this->roles = GetCandy::roles()->getHubAccessRoles();
    }

    /**
     * Resolves the scope if criteria is met.
     *
     * @param  \Closure  $callback
     * @return void
     */
    protected function resolve(\Closure $callback)
    {
        if (
            ! $this->getUser()
            || ! $this->canAccessHub()
            || ($this->canAccessHub() && ! GetCandy::isHubRequest())
        ) {
            $callback();
        }
    }

    /**
     * Gets the authenticated user.
     *
     * @return \Illuminate\Foundation\Auth\User
     */
    protected function getUser()
    {
        return app()->auth->user();
    }

    /**
     * Getter for hub access check.
     *
     * @return bool
     */
    protected function canAccessHub()
    {
        $user = $this->getUser();

        return $user ? $user->hasAnyRole($this->roles) : false;
    }

    protected function filterColumns(Builder $builder, array $incoming)
    {
        $existingColumns = $builder->getQuery()->columns ?: [];

        return collect($incoming)->filter(function ($column) use ($existingColumns) {
            return ! in_array($column, $existingColumns);
        });
    }

    /**
     * Get the customer groups.
     *
     * @return array
     */
    protected function getGroups()
    {
        $user = $this->getUser();
        $defaultGroup = FetchDefaultCustomerGroup::run();
        $guestGroups = [$defaultGroup->id];
        if (! $user) {
            return $guestGroups;
        }
        if (($this->canAccessHub() && GetCandy::isHubRequest()) ||
            (! GetCandy::isHubRequest() && ! $this->canAccessHub())
        ) {
            return $user->customer->customerGroups->pluck('id')->toArray();
        }

        return $guestGroups;
    }
}
