<?php

namespace GetCandy\Api\Core\Orders\Listeners;

use GetCandy\Api\Core\Discounts\Factory;
use GetCandy\Api\Core\Baskets\Events\BasketStoredEvent;

class SyncWithBasketListener
{
    protected $factory;

    public function __construct(Factory $factory)
    {
        $this->factory = $factory;
    }

    /**
     * Handle the event.
     *
     * @param  OrderShipped  $event
     * @return void
     */
    public function handle(BasketStoredEvent $event)
    {
        if (! $event->basket->order) {
            return true;
        }
        app('api')->orders()->syncWithBasket($event->basket->order, $event->basket);
    }
}
