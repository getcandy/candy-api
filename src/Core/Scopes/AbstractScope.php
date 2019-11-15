<?php

namespace GetCandy\Api\Core\Scopes;

use GetCandy\Api\Core\GetCandy;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

abstract class AbstractScope implements Scope
{
    /**
     * The Candy API instance.
     */
    protected $api;

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
     * @var Model
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
        $this->roles = app('api')->roles()->getHubAccessRoles();
        $this->api = app()->getInstance()->make(GetCandy::class);
    }

    /**
     * Resolves the scope if criteria is met.
     *
     * @param \Closure $callback
     * @return void
     */
    protected function resolve(\Closure $callback)
    {
        if (
            ! $this->getUser()
            || ! $this->canAccessHub()
            || ($this->canAccessHub() && ! $this->api->isHubRequest())
        ) {
            $callback();
        }
    }

    /**
     * Gets the authenticated user.
     *
     * @return Model
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

    /**
     * Get the customer groups.
     *
     * @return array
     */
    protected function getGroups()
    {
        $user = $this->getUser();
        $guestGroups = [app('api')->customerGroups()->getGuestId()];
        if (! $user) {
            return $guestGroups;
        }
        if (($this->canAccessHub() && $this->api->isHubRequest()) ||
            (! $this->api->isHubRequest() && ! $this->canAccessHub())
        ) {
            return $user->groups->pluck('id')->toArray();
        }

        return $guestGroups;
    }
}
