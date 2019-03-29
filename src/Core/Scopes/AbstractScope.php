<?php

namespace GetCandy\Api\Core\Scopes;

use Auth;
use GetCandy\Api\Core\CandyApi;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

abstract class AbstractScope implements Scope
{
    /**
     * The Candy API instance
     */
    protected $api;

    /**
     * The customer groups
     *
     * @var array
     */
    protected $groups;

    /**
     * The hub roles
     *
     * @var array
     */
    protected $roles;

    /**
     * The current user
     *
     * @var Model
     */
    protected $user;

    /**
     * Whether the user has Hub roles
     *
     * @var boolean
     */
    protected $hasHubRoles = false;

    public function __construct()
    {
        $this->roles = app('api')->roles()->getHubAccessRoles();
        $this->api = app()->getInstance()->make(CandyApi::class);
        $this->user = Auth::user();

        $this->groups = [app('api')->customerGroups()->getGuestId()];

        if ($this->user) {
            $this->hasHubRoles = $this->user->hasAnyRole($this->roles);
            $this->groups = $this->user->groups->pluck('id')->toArray();
            if ($this->hasHubRoles && !$this->api->isHubRequest()) {
                $this->groups = [app('api')->customerGroups()->getGuestId()];
            }
        }
    }
}