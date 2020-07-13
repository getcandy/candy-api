<?php

namespace GetCandy\Api\Providers;

use GetCandy\Api\Core\Facades\Route;
use Illuminate\Support\ServiceProvider;
use GetCandy\Api\Core\Routes\RouteFactory;
use GetCandy\Api\Core\Routes\RouteFactoryInterface;

class RouteServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->bind(RouteFactoryInterface::class, function ($app) {
            return $app->make(RouteFactory::class);
        });

        $this->app->bind(Route::class, function ($app) {
            return $app->make(RouteFactoryInterface::class);
        });
    }
}
