<?php

namespace GetCandy\Api\Providers;

use GetCandy\Api\Core\Orders\Events\OrderSavedEvent;
use GetCandy\Api\Core\Orders\Events\OrderRefundEvent;
use GetCandy\Api\Core\Baskets\Events\BasketStoredEvent;
use GetCandy\Api\Core\Search\Events\IndexableSavedEvent;
use GetCandy\Api\Core\Products\Events\ProductViewedEvent;
use GetCandy\Api\Core\Products\Events\ProductCreatedEvent;
use GetCandy\Api\Core\Products\Events\ProductUpdatedEvent;
use GetCandy\Api\Core\Search\Listeners\IndexObjectListener;
use GetCandy\Api\Core\Attributes\Events\AttributeSavedEvent;
use GetCandy\Api\Core\Orders\Listeners\RefreshOrderListener;
use GetCandy\Api\Core\Orders\Listeners\SyncWithBasketListener;
use GetCandy\Api\Core\Search\Listeners\UpdateMappingsListener;
use GetCandy\Api\Core\Attributes\Events\AttributableSavedEvent;
use GetCandy\Api\Core\ActivityLog\Listeners\Orders\LogRefundListener;
use GetCandy\Api\Core\Attributes\Listeners\SyncAttributablesListener;
use GetCandy\Api\Core\Discounts\Listeners\AddDiscountToProductListener;
use GetCandy\Api\Core\Products\Listeners\AddToIndexListener as ProductIndexListener;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event listener mappings for the application.
     *
     * @var array
     */
    protected $listen = [
        AttributableSavedEvent::class => [
            SyncAttributablesListener::class,
        ],
        AttributeSavedEvent::class => [
            UpdateMappingsListener::class,
        ],
        ProductCreatedEvent::class => [
            ProductIndexListener::class,
        ],
        ProductUpdatedEvent::class => [
            ProductIndexListener::class,
        ],
        ProductViewedEvent::class => [
            AddDiscountToProductListener::class,
        ],
        BasketStoredEvent::class => [
            SyncWithBasketListener::class,
        ],
        IndexableSavedEvent::class => [
            IndexObjectListener::class,
        ],
        OrderSavedEvent::class => [
            RefreshOrderListener::class,
        ],
        OrderRefundEvent::class => [
            LogRefundListener::class,
        ],
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
