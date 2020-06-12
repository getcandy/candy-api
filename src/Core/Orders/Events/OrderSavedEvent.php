<?php

namespace GetCandy\Api\Core\Orders\Events;

use GetCandy\Api\Core\Baskets\Models\Basket;
use GetCandy\Api\Core\Orders\Models\Order;

class OrderSavedEvent
{
    /**
     * @var \GetCandy\Api\Core\Orders\Models\Order
     */
    public $order;

    /**
     * @var \GetCandy\Api\Core\Baskets\Models\Basket
     */
    public $basket;

    /**
     * Create a new event instance.
     *
     * @param  \GetCandy\Api\Core\Orders\Models\Order  $order
     * @param  null|\GetCandy\Api\Core\Baskets\Models\Basket  $basket
     * @return void
     */
    public function __construct(Order $order, Basket $basket = null)
    {
        $this->order = $order;
        $this->basket = $basket ?: $order->basket;
    }
}
