<?php

namespace GetCandy\Api\Providers;

use GetCandy\Api\Core\Pages\Services\PageService;
use Illuminate\Support\ServiceProvider;

class PageServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->bind('getcandy.pages', function ($app) {
            return $app->make(PageService::class);
        });
    }
}
