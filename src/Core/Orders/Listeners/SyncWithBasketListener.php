<?php

namespace GetCandy\Api\Core\Orders\Listeners;

use GetCandy\Api\Core\Discounts\Factory;
use GetCandy\Api\Core\Baskets\Events\BasketStoredEvent;
use GetCandy\Api\Core\Orders\Interfaces\OrderFactoryInterface;

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
        if (!$event->basket->activeOrder) {
            return true;
        }
        app(OrderFactoryInterface::class)
            ->basket($event->basket)
            ->order($event->basket->activeOrder)
            ->resolve();
    }
}
