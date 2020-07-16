<?php

namespace GetCandy\Api\Providers;

use GetCandy\Api\Core\Layouts\Services\LayoutService;
use Illuminate\Support\ServiceProvider;

class LayoutServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->bind('getcandy.layouts', function ($app) {
            return $app->make(LayoutService::class);
        });
    }
}
