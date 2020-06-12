<?php

namespace GetCandy\Api\Core\Orders\Listeners;

use GetCandy\Api\Core\Baskets\Events\BasketStoredEvent;
use GetCandy\Api\Core\Discounts\Factory;

class SyncWithBasketListener
{
    /**
     * @var \GetCandy\Api\Core\Discounts\Factory
     */
    protected $factory;

    public function __construct(Factory $factory)
    {
        $this->factory = $factory;
    }

    /**
     * Handle the event.
     *
     * @param  \GetCandy\Api\Core\Baskets\Events\BasketStoredEvent  $event
     * @return void
     */
    public function handle(BasketStoredEvent $event)
    {
        if (! $event->basket->activeOrder) {
            return true;
        }
        app(OrderFactoryInterface::class)
            ->basket($event->basket)
            ->order($event->basket->activeOrder)
            ->resolve();
    }
}
