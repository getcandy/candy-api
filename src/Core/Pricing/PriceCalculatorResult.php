<?php

namespace GetCandy\Api\Core\Pricing;

class PriceCalculatorResult implements PriceCalculatorInterface
{
    protected $baseCost;
    protected $factorTax;
    protected $unitCost;
    protected $unitTax;
    protected $factor;
    protected $totalCost;
    protected $totalTax;
    protected $qty;

    public function __construct($data = [])
    {
        foreach ($data as $key => $value) {
            if (property_exists($this, $key)) {
                $this->{$key} = $value;
            }
        }
    }

    public function __get($property)
    {
        $prop = camel_case($property);
        if (property_exists($this, $prop)) {
            return $this->{$prop};
        }
        throw new \InvalidArgumentException("Method or Property {$property} doesn't exist");
    }
}
