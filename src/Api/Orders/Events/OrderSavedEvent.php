<?php

namespace GetCandy\Api\Orders\Events;

use GetCandy\Api\Orders\Models\Order;

class OrderSavedEvent
{
    public $order;

    /**
     * Create a new event instance.
     *
     * @param  Order  $order
     * @return void
     */
    public function __construct(Order $order)
    {
        $this->order = $order;
    }
}
