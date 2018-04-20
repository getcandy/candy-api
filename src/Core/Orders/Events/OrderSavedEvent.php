<?php

namespace GetCandy\Api\Core\Orders\Events;

use GetCandy\Api\Core\Orders\Models\Order;

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
