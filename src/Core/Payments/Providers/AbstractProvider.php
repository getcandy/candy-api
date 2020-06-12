<?php

namespace GetCandy\Api\Core\Payments\Providers;

use GetCandy\Api\Core\Orders\Models\Order;

abstract class AbstractProvider
{
    protected $billing;

    /**
     * The order to process.
     *
     * @var \GetCandy\Api\Core\Orders\Models\Order
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
     * @var null|string
     */
    protected $token = null;

    /**
     * Set the order.
     *
     * @param  \GetCandy\Api\Core\Orders\Models\Order  $order
     * @return $this
     */
    public function order(Order $order)
    {
        $this->order = $order;

        return $this;
    }

    /**
     * Set additional fields.
     *
     * @param  array  $fields
     * @return $this
     */
    public function fields(array $fields)
    {
        $this->fields = $fields;

        return $this;
    }

    /**
     * Set the payment token.
     *
     * @param  string  $token
     * @return $this
     */
    public function token($token)
    {
        $this->token = $token;

        return $this;
    }

    /**
     * Validate the payment token.
     *
     * @param  string  $token
     * @return void
     */
    abstract public function validate($token);

    /**
     * Gets the name of the provider.
     *
     * @return string
     */
    abstract public function getName();

    /**
     * Create a charge for a payment token.
     *
     * @return void
     */
    abstract public function charge();

    /**
     * Refund a transaction.
     *
     * @param  string  $token
     * @param  mixed  $amount
     * @param  mixed  $description
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
