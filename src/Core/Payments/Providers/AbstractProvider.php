<?php

namespace GetCandy\Api\Core\Payments\Providers;

use GetCandy\Api\Core\Orders\Models\Order;

abstract class AbstractProvider
{
    protected $billing;

    /**
     * Gets the name of the provider.
     *
     * @return string
     */
    abstract public function getName();

    /**
     * Validates a payment token.
     *
     * @param string $token
     *
     * @return bool
     */
    abstract public function validateToken($token);

    /**
     * Create a charge for a payment token.
     *
     * @param string $token
     *
     * @return void
     */
    abstract public function charge($token, Order $order);

    /**
     * Refund a transaction.
     *
     * @param string $token
     * @param mixed $amount
     *
     * @return void
     */
    abstract public function refund($token, $amount = null);

    /**
     * Gets a client token for the front end.
     *
     * @return mixed
     */
    abstract public function getClientToken();
}
