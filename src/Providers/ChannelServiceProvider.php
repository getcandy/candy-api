<?php

namespace GetCandy\Api\Providers;

use GetCandy\Api\Core\Channels\Factories\ChannelFactory;
use GetCandy\Api\Core\Channels\Interfaces\ChannelFactoryInterface;
use GetCandy\Api\Core\Channels\Models\Channel;
use GetCandy\Api\Core\Channels\Observers\ChannelObserver;
use Illuminate\Support\ServiceProvider;

class ChannelServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->singleton(ChannelFactoryInterface::class, function ($app) {
            return $app->make(ChannelFactory::class);
        });
        Channel::observe(ChannelObserver::class);
    }
}
