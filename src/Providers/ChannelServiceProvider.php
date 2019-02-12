<?php

namespace GetCandy\Api\Providers;

use Illuminate\Support\ServiceProvider;
use GetCandy\Api\Core\Channels\Factories\ChannelFactory;
use GetCandy\Api\Core\Channels\Interfaces\ChannelFactoryInterface;

class ChannelServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->singleton(ChannelFactoryInterface::class, function ($app) {
            return $app->make(ChannelFactory::class);
        });
    }
}
