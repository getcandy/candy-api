<?php

namespace GetCandy\Api\Providers;

use GetCandy\Api\Core\Search\Commands\IndexCategoriesCommand;
use GetCandy\Api\Core\Search\Commands\IndexProductsCommand;
use GetCandy\Api\Core\Search\Commands\ScoreProductsCommand;
use GetCandy\Api\Core\Search\Contracts\SearchManagerContract;
use GetCandy\Api\Core\Search\SearchManager;
use Illuminate\Support\ServiceProvider;

class SearchServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->singleton(SearchManagerContract::class, function ($app) {
            return new SearchManager($app);
        });

        if ($this->app->runningInConsole()) {
            $this->commands([
                IndexProductsCommand::class,
                IndexCategoriesCommand::class,
                ScoreProductsCommand::class,
            ]);
        }
    }
}
