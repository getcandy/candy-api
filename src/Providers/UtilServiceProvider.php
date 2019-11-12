<?php

namespace GetCandy\Api\Providers;

use GetCandy\Api\Core\Utils\Import\ImportManager;
use GetCandy\Api\Core\Utils\Import\ImportManagerContract;
use Illuminate\Support\ServiceProvider;

class UtilServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->singleton(ImportManagerContract::class, function ($app) {
            return new ImportManager($app);
        });
    }
}
