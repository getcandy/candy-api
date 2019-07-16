<?php

namespace GetCandy\Api\Core\Pricing;

use GetCandy\Api\Core\Taxes\Interfaces\TaxCalculatorInterface;
use GetCandy\Api\Core\Currencies\Interfaces\CurrencyConverterInterface;

class PriceCalculator implements PriceCalculatorInterface
{
    /**
     * The currency converter instance.
     *
     * @var CurrencyConverterInterface
     */
    protected $converter;

    /**
     * The tax calculator instance.
     *
     * @var TaxCalculatorInterface
     */
    protected $taxes;

    public function __construct(CurrencyConverterInterface $converter, TaxCalculatorInterface $taxes)
    {
        $this->converter = $converter;
        $this->taxes = $taxes;
    }

    public function get($price, $tax = 0, $qty = 1, $factor = 1)
    {
        $unitPrice = $price / $factor;

        $converted = $this->converter->convert($unitPrice * $qty);
        // 0.8726
        $taxamount = $this->taxes->setTax($tax)->amount($converted);
        $factorTax = $this->taxes->setTax($tax)->amount($price);

        return new PriceCalculatorResult([
            'baseCost' => $this->converter->convert($price),
            'factorTax' => round($this->converter->convert($factorTax), 2),
            'unitCost' => $this->converter->convert($unitPrice),
            'unitTax' => round($taxamount / $qty, 2),
            'factor' => $factor,
            'totalCost' => $converted,
            'totalTax' => $taxamount,
            'qty' => (int) $qty,
        ]);
    }
}
