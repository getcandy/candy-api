<?php

namespace GetCandy\Api\Core\Pricing;

use TaxCalculator;
use CurrencyConverter;
use InvalidArgumentException;

class PriceCalculatorService
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

        if ($tax == 'default') {
            $taxamount = TaxCalculator::amount($converted);
        } else {
            $taxamount = TaxCalculator::set($tax)->amount($converted);
        }

        $this->pricing = [
            'amount' => $converted,
            'tax' => $taxamount,
        ];

        return $this;
    }
}
