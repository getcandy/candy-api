<?php

namespace GetCandy\Api\Providers;

use GetCandy\Api\Attributes\Events\AttributableSavedEvent;
use GetCandy\Api\Attributes\Listeners\SyncAttributablesListener;
use GetCandy\Api\Baskets\Events\BasketStoredEvent;
use GetCandy\Api\Discounts\Listeners\AddDiscountToProductListener;
use GetCandy\Api\Orders\Listeners\SyncWithBasketListener;
use GetCandy\Api\Products\Events\ProductCreatedEvent;
use GetCandy\Api\Products\Events\ProductUpdatedEvent;
use GetCandy\Api\Products\Events\ProductViewedEvent;
use GetCandy\Api\Search\Events\IndexableSavedEvent;
use GetCandy\Api\Search\Listeners\IndexObjectListener;
use GetCandy\Api\Products\Listeners\AddToIndexListener as ProductIndexListener;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Event;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event listener mappings for the application.
     *
     * @var array
     */
    protected $listen = [
        AttributableSavedEvent::class => [
            SyncAttributablesListener::class
        ],
        ProductCreatedEvent::class => [
            ProductIndexListener::class
        ],
        ProductUpdatedEvent::class => [
            ProductIndexListener::class
        ],
        ProductViewedEvent::class => [
            AddDiscountToProductListener::class
        ],
        BasketStoredEvent::class => [
            SyncWithBasketListener::class
        ],
        IndexableSavedEvent::class => [
            IndexObjectListener::class
        ]
    ];

    /**
     * Register any events for your application.
     *
     * @return void
     */
    public function boot()
    {
        parent::boot();
    }
}
