<?php

namespace GetCandy\Api\Providers;

use Illuminate\Support\ServiceProvider;
use GetCandy\Api\Core\Layouts\Services\LayoutService;

class LayoutServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->singleton('getcandy.layouts', function ($app) {
            return $app->make(LayoutService::class);
        });
    }
}
