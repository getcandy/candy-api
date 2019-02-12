<?php

namespace GetCandy\Api\Providers;

use Illuminate\Support\ServiceProvider;
use GetCandy\Api\Core\Search\SearchContract;
use GetCandy\Api\Core\Search\Factories\SearchResultFactory;
use GetCandy\Api\Core\Search\Interfaces\SearchResultInterface;

class SearchServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->singleton(SearchContract::class, function ($app) {
            return $app->make(config('getcandy.search.client'));
        });

        $this->app->bind(SearchResultInterface::class, function ($app) {
            return $app->make(SearchResultFactory::class);
        });
    }
}
