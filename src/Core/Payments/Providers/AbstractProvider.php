<?php

namespace GetCandy\Api\Core\Payments\Providers;

use GetCandy\Api\Core\Orders\Models\Order;

abstract class AbstractProvider
{
    protected $billing;

    /**
     * The order to process.
     *
     * @var Order
     */
    protected $order;

    /**
     * Any additional fields.
     *
     * @var array
     */
    protected $fields = [];

    /**
     * The payment token.
     *
     * @var string
     */
    protected $token = null;

    /**
     * Set the order.
     *
     * @param Order $order
     * @return AbstractProvider
     */
    public function order(Order $order)
    {
        $this->order = $order;

        return $this;
    }

    /**
     * Set additional fields.
     *
     * @param array $fields
     * @return AbstractProvider
     */
    public function fields(array $fields)
    {
        $this->fields = $fields;

        return $this;
    }

    /**
     * Set the payment token.
     *
     * @param string $token
     * @return AbstractToken
     */
    public function token($token)
    {
        $this->token = $token;

        return $this;
    }

    /**
     * Validate the payment token.
     *
     * @param string $token
     * @return void
     */
    abstract public function validate($token);

    /**
     * Gets the name of the provider.
     * @return string
     */
    abstract public function getName();

    /**
     * Create a charge for a payment token.
     *
     * @param string $token
     *
     * @return void
     */
    abstract public function charge();

    /**
     * Refund a transaction.
     *
     * @param string $token
     * @param mixed $amount
     *
     * @return void
     */
    abstract public function refund($token, $amount, $description);

    /**
     * Gets a client token for the front end.
     *
     * @return mixed
     */
    abstract public function getClientToken();
}
