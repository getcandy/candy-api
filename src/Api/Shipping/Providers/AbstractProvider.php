<?php

namespace GetCandy\Api\Shipping\Providers;

abstract class AbstractProvider
{
    /**
     * Order
     *
     * @var Order
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
