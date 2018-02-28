<?php
namespace GetCandy\Api\Orders\Listeners;

use GetCandy\Api\Attributes\Events\AttributableSavedEvent;
use GetCandy\Api\Baskets\Events\BasketStoredEvent;
use GetCandy\Api\Discounts\Factory;

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
        if (!$event->basket->order) {
            return true;
        }
        app('api')->orders()->syncWithBasket($event->basket->order, $event->basket);
    }
}
