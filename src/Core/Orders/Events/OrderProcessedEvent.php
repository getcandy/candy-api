<?php

namespace GetCandy\Api\Core\Orders\Events;

use GetCandy\Api\Core\Orders\Models\Order;

class OrderProcessedEvent
{
    /**
     * @var \GetCandy\Api\Core\Orders\Models\Order
     */
    public $order;

    /**
     * Create a new event instance.
     *
     * @param  \GetCandy\Api\Core\Orders\Models\Order  $order
     * @return void
     */
    public function __construct(Order $order)
    {
        $this->order = $order;
    }
}
