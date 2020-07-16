<?php

namespace GetCandy\Api\Providers;

use Illuminate\Support\ServiceProvider;
use GetCandy\Api\Core\Tags\Services\TagService;

class TagServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->bind('getcandy.tags', function ($app) {
            return $app->make(TagService::class);
        });
    }
}
