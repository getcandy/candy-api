<?php

namespace GetCandy\Api\Providers;

use GetCandy\Api\Core\Auth\Services\RoleService;
use GetCandy\Api\Core\Auth\Services\UserService;
use Illuminate\Support\ServiceProvider;

class UserServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->singleton('getcandy.roles', function ($app) {
            return $app->make(RoleService::class);
        });

        $this->app->bind('getcandy.users', function ($app) {
            return $app->make(UserService::class);
        });

    }
}
