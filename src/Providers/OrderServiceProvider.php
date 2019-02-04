<?php

namespace GetCandy\Api\Providers;

use Illuminate\Support\ServiceProvider;
use GetCandy\Api\Core\Orders\OrderCriteria;
use GetCandy\Api\Core\Orders\Services\OrderService;
use GetCandy\Api\Core\Orders\Factories\OrderFactory;
use GetCandy\Api\Core\Orders\Factories\OrderProcessingFactory;
use GetCandy\Api\Core\Orders\Interfaces\OrderFactoryInterface;
use GetCandy\Api\Core\Orders\Interfaces\OrderServiceInterface;
use GetCandy\Api\Core\Orders\Interfaces\OrderCriteriaInterface;
use GetCandy\Api\Core\Orders\Interfaces\OrderProcessingFactoryInterface;

class OrderServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->bind(OrderCriteriaInterface::class, function ($app) {
            return $app->make(OrderCriteria::class);
        });

        $this->app->bind(OrderServiceInterface::class, function ($app) {
            return $app->make(OrderService::class);
        });

        $this->app->bind(OrderFactoryInterface::class, function ($app) {
            return $app->make(OrderFactory::class);
        });

        $this->app->bind(OrderProcessingFactoryInterface::class, function ($app) {
            return $app->make(OrderProcessingFactory::class);
        });
    }
}
