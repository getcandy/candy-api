<?php

namespace GetCandy\Api\Providers;

use GetCandy\Api\Core\Payments\PaymentContract;
use GetCandy\Api\Core\Payments\PaymentManager;
use Illuminate\Support\ServiceProvider;

class PaymentServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->singleton(PaymentContract::class, function ($app) {
            return new PaymentManager($app);
        });
    }
}
