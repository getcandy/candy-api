<?php

namespace GetCandy\Api\Core\Shipping\Providers;

abstract class AbstractProvider
{
    /**
     * @var \GetCandy\Api\Core\Orders\Models\Order
     */
    protected $order;

    protected $method;

    public function __construct($method)
    {
        $this->method = $method;
    }

    abstract public function calculate($order);

    protected function getBasket()
    {
        return $this->order->basket;
    }
}
