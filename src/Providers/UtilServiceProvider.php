<?php

namespace GetCandy\Api\Providers;

use Illuminate\Support\ServiceProvider;
use GetCandy\Api\Core\Utils\Import\ImportManagerContract;
use GetCandy\Api\Core\Utils\Import\ImportManager;

class UtilServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->singleton(ImportManagerContract::class, function ($app) {
            return new ImportManager($app);
        });
    }
}
