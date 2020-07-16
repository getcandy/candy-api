<?php

namespace GetCandy\Api\Providers;

use GetCandy\Api\Core\Payments\PaymentContract;
use GetCandy\Api\Core\Payments\PaymentManager;
use GetCandy\Api\Core\Payments\Services\PaymentService;
use GetCandy\Api\Core\Payments\Services\PaymentTypeService;
use Illuminate\Support\ServiceProvider;

class PaymentServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->singleton(PaymentContract::class, function ($app) {
            return new PaymentManager($app);
        });

        $this->app->bind('getcandy.payments', function ($app) {
            return $app->make(PaymentService::class);
        });

        $this->app->bind('getcandy.payment_types', function ($app) {
            return $app->make(PaymentTypeService::class);
        });
    }
}
