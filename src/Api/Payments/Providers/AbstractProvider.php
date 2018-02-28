<?php

namespace GetCandy\Api\Payments\Providers;

abstract class AbstractProvider
{
    protected $billing;

    /**
     * Gets the name of the provider
     *
     * @return string
     */
    abstract public function getName();

    /**
     * Validates a payment token
     *
     * @param string $token
     *
     * @return boolean
     */
    abstract public function validateToken($token);

    /**
     * Create a charge for a payment token
     *
     * @param string $token
     *
     * @return void
     */
    abstract public function charge($token, $order);

    /**
     * Refund a transaction
     *
     * @param string $token
     * @param mixed $amount
     *
     * @return void
     */
    abstract public function refund($token, $amount = null);
}
