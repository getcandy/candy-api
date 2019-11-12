<?php

namespace GetCandy\Api\Providers;

use GetCandy\Api\Core\Reports\Contracts\ReportManagerContract;
use GetCandy\Api\Core\Reports\ReportManager;
use Illuminate\Support\ServiceProvider;

class ReportsServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->bind(ReportManagerContract::class, function ($app) {
            return new ReportManager($app);
        });
    }
}
