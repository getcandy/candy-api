<?php

namespace GetCandy\Api\Providers;

use GetCandy\Api\Core\Attributes\Services\AttributeGroupService;
use GetCandy\Api\Core\Attributes\Services\AttributeService;
use Illuminate\Support\ServiceProvider;

class AttributeServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->bind('getcandy.attributes', function ($app) {
            return $app->make(AttributeService::class);
        });
        $this->app->bind('getcandy.attribute_groups', function ($app) {
            return $app->make(AttributeGroupService::class);
        });
    }
}
