<?php

namespace GetCandy\Api\Core\Scaffold;

use GetCandy\Api\Core\Pricing\PriceCalculatorInterface;

abstract class AbstractFactory
{
    /**
     * The price calculator instance.
     *
     * @var PriceCalculatorInterface
     */
    protected $calculator;

    public function __construct(PriceCalculatorInterface $calculator)
    {
        $this->calculator = $calculator;
    }
}
