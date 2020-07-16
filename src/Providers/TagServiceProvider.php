<?php

namespace GetCandy\Api\Providers;

use GetCandy\Api\Core\Tags\Services\TagService;
use Illuminate\Support\ServiceProvider;

class TagServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->bind('getcandy.tags', function ($app) {
            return $app->make(TagService::class);
        });
    }
}
