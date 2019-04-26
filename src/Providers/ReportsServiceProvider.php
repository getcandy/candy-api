<?php

namespace GetCandy\Api\Providers;

use Illuminate\Support\ServiceProvider;
use GetCandy\Api\Core\Reports\ReportManager;
use GetCandy\Api\Core\Reports\Contracts\ReportManagerContract;

class ReportsServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->bind(ReportManagerContract::class, function ($app) {
            return new ReportManager($app);
        });
    }
}
