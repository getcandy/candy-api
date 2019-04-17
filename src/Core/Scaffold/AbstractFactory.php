<?php

namespace GetCandy\Api\Core\Scaffold;

use GetCandy\Api\Core\CandyApi;
use GetCandy\Api\Core\Pricing\PriceCalculatorInterface;

abstract class AbstractFactory
{
    /**
     * The price calculator instance.
     *
     * @var PriceCalculatorInterface
     */
    protected $calculator;

    /**
     * The CandyApi manager.
     *
     * @var CandyApi
     */
    protected $api;

    public function __construct(PriceCalculatorInterface $calculator, CandyApi $api)
    {
        $this->calculator = $calculator;
        $this->api = $api;
    }
}
