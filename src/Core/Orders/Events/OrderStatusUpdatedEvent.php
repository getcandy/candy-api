<?php

namespace GetCandy\Api\Core\Orders\Events;

use GetCandy\Api\Core\Orders\Models\Order;

class OrderStatusUpdatedEvent
{
    /**
     * @var \GetCandy\Api\Core\Orders\Models\Order
     */
    public $order;

    /**
     * Create a new event instance.
     *
     * @param  \GetCandy\Api\Core\Orders\Models\Order  $order
     * @param  null|\GetCandy\Api\Core\Baskets\Models\Basket  $basket
     * @return void
     */
    public function __construct(Order $order)
    {
        $this->order = $order;
    }
}
