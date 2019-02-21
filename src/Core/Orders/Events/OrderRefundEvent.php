<?php

namespace GetCandy\Api\Core\Orders\Events;

use GetCandy\Api\Core\Orders\Models\Order;
use GetCandy\Api\Core\Payments\Models\Transaction;

class OrderRefundEvent
{
    /**
     * The related order.
     *
     * @var Order
     */
    public $order;

    /**
     * The refunded transaction.
     *
     * @var Transaction
     */
    public $transaction;

    /**
     * Create a new event instance.
     *
     * @param  Order  $order
     * @return void
     */
    public function __construct(Order $order, Transaction $transaction)
    {
        $this->order = $order;
        $this->transaction = $transaction;
    }
}
