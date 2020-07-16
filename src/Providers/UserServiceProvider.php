<?php

namespace GetCandy\Api\Providers;

use GetCandy\Api\Core\Auth\Services\RoleService;
use GetCandy\Api\Core\Users\Services\UserService;
use Illuminate\Support\ServiceProvider;

class UserServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->singleton('getcandy.users', function ($app) {
            return $app->make(UserService::class);
        });

        $this->app->singleton('getcandy.roles', function ($app) {
            return $app->make(RoleService::class);
        });
    }
}
