<?php

namespace GetCandy\Api\Providers;

use Illuminate\Support\ServiceProvider;
use GetCandy\Api\Core\Users\Services\UserService;

class UserServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->singleton('getcandy.users', function ($app) {
            return $app->make(UserService::class);
        });
    }
}
