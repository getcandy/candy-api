<?php

namespace GetCandy\Api\Pricing;

use TaxCalculator;
use CurrencyConverter;
use InvalidArgumentException;

class PriceCalculator
{
    protected $pricing = [];

    public function __get($property)
    {
        if (isset($this->pricing[$property])) {
            return $this->pricing[$property];
        }
        throw new InvalidArgumentException("Method or Property {$property} doesn't exist");
    }

    public function get($price, $tax = 0)
    {
        $converted = CurrencyConverter::convert($price);
        $taxamount = TaxCalculator::set($tax)->amount($converted);

        $this->pricing = [
            'amount' => round($converted + $taxamount, 2),
            'tax' => $taxamount
        ];
        return $this;
    }
}
