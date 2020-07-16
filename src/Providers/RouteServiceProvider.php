<?php

namespace GetCandy\Api\Providers;

use Illuminate\Support\ServiceProvider;
use GetCandy\Api\Core\Routes\Services\RouteService;

class RouteServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->bind('getcandy.routes', function ($app) {
            return $app->make(RouteService::class);
        });
    }
}
