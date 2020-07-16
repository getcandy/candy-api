<?php

namespace GetCandy\Api\Providers;

use Illuminate\Support\ServiceProvider;
use GetCandy\Api\Core\Attributes\Services\AttributeService;

class AttributeServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->bind('getcandy.attributes', function ($app) {
            return $app->make(AttributeService::class);
        });
    }
}
