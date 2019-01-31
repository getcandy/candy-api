<?php

namespace GetCandy\Api\Core\Pricing;

use GetCandy\Api\Core\Currencies\Interfaces\CurrencyConverterInterface;
use GetCandy\Api\Core\Taxes\Interfaces\TaxCalculatorInterface;

class PriceCalculator implements PriceCalculatorInterface
{
    protected $pricing = [];

    /**
     * The currency converter instance
     *
     * @var CurrencyConverterInterface
     */
    protected $converter;

    /**
     * The tax calculator instance
     *
     * @var TaxCalculatorInterface
     */
    protected $taxes;

    public function __construct(CurrencyConverterInterface $converter, TaxCalculatorInterface $taxes)
    {
        $this->converter = $converter;
        $this->taxes = $taxes;
    }

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

        $converted = $this->converter->convert($unitPrice * $qty);

        if ($tax == 'default') {
            $taxamount = $this->taxes->amount($converted);
        } else {
            $taxamount = $this->taxes->setTax($tax)->amount($converted);
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
