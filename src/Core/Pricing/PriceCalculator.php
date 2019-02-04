<?php

namespace GetCandy\Api\Core\Pricing;

use GetCandy\Api\Core\Currencies\Interfaces\CurrencyConverterInterface;
use GetCandy\Api\Core\Taxes\Interfaces\TaxCalculatorInterface;

class PriceCalculator implements PriceCalculatorInterface
{
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

    public function get($price, $tax = 0, $qty = 1, $factor = 1)
    {
        $unitPrice = $price / $factor;

        $converted = $this->converter->convert($unitPrice * $qty);

        if ($tax == 'default') {
            $taxamount = $this->taxes->amount($converted);
            $factorTax = $this->taxes->amount($price);
        } else {
            $taxamount = $this->taxes->setTax($tax)->amount($converted);
            $factorTax = $this->taxes->amount($price);
        }

        return new PriceCalculatorResult([
            'baseCost' => $price,
            'factorTax' => $factorTax,
            'unitCost' => $unitPrice,
            'unitTax' => round($taxamount / $qty, 2),
            'factor' => $factor,
            'totalCost' => round($converted, 2),
            'totalTax' => $taxamount,
            'qty' => (int) $qty,
        ]);
    }
}
