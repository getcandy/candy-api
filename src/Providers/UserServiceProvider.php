<?php

namespace GetCandy\Api\Providers;

use GetCandy\Api\Core\Auth\Services\RoleService;
use Illuminate\Support\ServiceProvider;

class UserServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->singleton('getcandy.roles', function ($app) {
            return $app->make(RoleService::class);
        });
    }
}
