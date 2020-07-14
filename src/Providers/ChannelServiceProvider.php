<?php

namespace GetCandy\Api\Providers;

use GetCandy\Api\Core\Channels\Factories\ChannelFactory;
use GetCandy\Api\Core\Channels\Interfaces\ChannelFactoryInterface;
use GetCandy\Api\Core\Channels\Services\ChannelService;
use Illuminate\Support\ServiceProvider;
class ChannelServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->singleton(ChannelFactoryInterface::class, function ($app) {
            return $app->make(ChannelFactory::class);
        });

        $this->app->bind('getcandy.channels', function ($app) {
            return $app->make(ChannelService::class);
        });
    }
}
