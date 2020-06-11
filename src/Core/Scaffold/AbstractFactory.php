<?php

namespace GetCandy\Api\Core\Scaffold;

use GetCandy\Api\Core\GetCandy;
use GetCandy\Api\Core\Pricing\PriceCalculatorInterface;

abstract class AbstractFactory
{
    /**
     * The price calculator instance.
     *
     * @var \GetCandy\Api\Core\Pricing\PriceCalculatorInterface
     */
    protected $calculator;

    /**
     * The GetCandy manager.
     *
     * @var \GetCandy\Api\Core\GetCandy
     */
    protected $api;

    public function __construct(PriceCalculatorInterface $calculator, GetCandy $api)
    {
        $this->calculator = $calculator;
        $this->api = $api;
    }
}
