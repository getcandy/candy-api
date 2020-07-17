<?php

namespace GetCandy\Api\Providers;

use Drafting;
use GetCandy\Api\Core\Categories\Drafting\CategoryDrafter;
use GetCandy\Api\Core\Categories\Services\CategoryService;
use GetCandy\Api\Core\Categories\Versioning\CategoryVersioner;
use Illuminate\Support\ServiceProvider;
use Versioning;

class CategoryServiceProvider extends ServiceProvider
{
    public function boot()
    {
        Drafting::extend('categories', function ($app) {
            return $app->make(CategoryDrafter::class);
        });

        Versioning::extend('categories', function ($app) {
            return $app->make(CategoryVersioner::class);
        });

        $this->app->bind('getcandy.categories', function ($app) {
            return $app->make(CategoryService::class);
        });
    }
}
