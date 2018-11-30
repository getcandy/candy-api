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

    public function get($price, $tax = 0, $qty = 1, $factor = 1)
    {
        $unitPrice = $price / $factor;

        $converted = CurrencyConverter::convert($unitPrice * $qty);

        if ($tax == 'default') {
            $taxamount = TaxCalculator::amount($converted);
        } else {
            $taxamount = TaxCalculator::set($tax)->amount($converted);
        }

        $this->pricing = [
            'base_cost' => $price,
            'unit_cost' => $unitPrice,
            'unit_tax' => round($taxamount / $qty, 2),
            'factor' => $factor,
            'total_cost' => round($converted, 2),
            'total_tax' => $taxamount,
            'qty' => (int) $qty,
        ];

        return $this;
    }
}
