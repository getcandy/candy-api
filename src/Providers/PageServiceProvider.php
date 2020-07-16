<?php

namespace GetCandy\Api\Providers;

use Illuminate\Support\ServiceProvider;
use GetCandy\Api\Core\Pages\Services\PageService;

class PageServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->bind('getcandy.pages', function ($app) {
            return $app->make(PageService::class);
        });
    }
}
