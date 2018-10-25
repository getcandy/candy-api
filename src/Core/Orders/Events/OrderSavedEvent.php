<?php

namespace GetCandy\Api\Core\Orders\Events;

use GetCandy\Api\Core\Orders\Models\Order;
use GetCandy\Api\Core\Baskets\Models\Basket;

class OrderSavedEvent
{
    public $order;

    public $basket;

    /**
     * Create a new event instance.
     *
     * @param  Order  $order
     * @return void
     */
    public function __construct(Order $order, Basket $basket = null)
    {
        $this->order = $order;
        $this->basket = $basket ?: $order->basket;
    }
}
