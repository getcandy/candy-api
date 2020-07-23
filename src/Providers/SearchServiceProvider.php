<?php

namespace GetCandy\Api\Providers;

use GetCandy\Api\Core\Search\SearchContract;
use GetCandy\Api\Core\Search\Services\SavedSearchService;
use Illuminate\Support\ServiceProvider;

class SearchServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->singleton(SearchContract::class, function ($app) {
            return $app->make(config('getcandy.search.client'));
        });

        $this->app->bind('getcandy.saved_search', function ($app) {
            return $app->make(SavedSearchService::class);
        });
    }
}
