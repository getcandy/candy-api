<?php

namespace GetCandy\Api\Core\Pricing;

use Illuminate\Support\Facades\Facade;

class PriceCalculator extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return new PriceCalculatorService();
    }
}
