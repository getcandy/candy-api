<?php

namespace GetCandy\Api\Core\Orders\Events;

use GetCandy\Api\Core\Orders\Models\Order;
use GetCandy\Api\Core\Payments\Models\Transaction;

class OrderRefundEvent
{
    /**
     * The related order.
     *
     * @var \GetCandy\Api\Core\Orders\Models\Order
     */
    public $order;

    /**
     * The refunded transaction.
     *
     * @var \GetCandy\Api\Core\Payments\Models\Transaction
     */
    public $transaction;

    /**
     * Create a new event instance.
     *
     * @param  \GetCandy\Api\Core\Orders\Models\Order  $order
     * @param  \GetCandy\Api\Core\Payments\Models\Transaction  $transaction
     * @return void
     */
    public function __construct(Order $order, Transaction $transaction)
    {
        $this->order = $order;
        $this->transaction = $transaction;
    }
}
