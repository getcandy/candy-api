<?php

namespace GetCandy\Api\Providers;

use Illuminate\Support\ServiceProvider;
use GetCandy\Api\Core\Payments\PaymentManager;
use GetCandy\Api\Core\Payments\PaymentContract;

class PaymentServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->singleton(PaymentContract::class, function ($app) {
            return new PaymentManager($app);
        });
    }
}
