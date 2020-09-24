<?php

namespace GetCandy\Api\Core\Orders\Listeners;

use GetCandy;
use GetCandy\Api\Core\Orders\Events\OrderSavedEvent;

class RefreshOrderListener
{
    /**
     * Handle the event.
     *
     * @param  \GetCandy\Api\Core\Orders\Events\OrderSavedEvent  $event
     * @return void
     */
    public function handle(OrderSavedEvent $event)
    {
        $order = $event->order;
        // If the order has been placed DO NOT alter it, no matter what.
        if ($order->placed_at) {
            return;
        }
        GetCandy::orders()->recalculate($order);
    }
}
