<?php

namespace GetCandy\Api\Providers;

use GetCandy\Api\Core\Routes\Services\RouteService;
use Illuminate\Support\ServiceProvider;

class RouteServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->bind('getcandy.routes', function ($app) {
            return $app->make(RouteService::class);
        });
    }
}
