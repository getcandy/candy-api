<?php

namespace GetCandy\Api\Providers;

use Drafting;
use Versioning;
use Illuminate\Support\ServiceProvider;
use GetCandy\Api\Core\Categories\Models\Category;
use GetCandy\Api\Core\Categories\Commands\RebuildTreeCommand;
use GetCandy\Api\Core\Categories\Drafting\CategoryDrafter;
use GetCandy\Api\Core\Categories\Services\CategoryService;
use GetCandy\Api\Core\Categories\Observers\CategoryObserver;
use GetCandy\Api\Core\Categories\Versioning\CategoryVersioner;

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

        Category::observe(CategoryObserver::class);

        if ($this->app->runningInConsole()) {
            $this->commands([
                RebuildTreeCommand::class,
            ]);
        }
    }
}
