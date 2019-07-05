<?php

namespace GetCandy\Api\Core\Taxes;

use GetCandy\Api\Core\Taxes\Models\Tax;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use GetCandy\Api\Core\Taxes\Interfaces\TaxCalculatorInterface;

class TaxCalculator implements TaxCalculatorInterface
{
    protected $rate;

    protected $taxable = false;

    protected $percent = 0;

    public function setTax($type = null)
    {
        if ($type) {
            $this->set($type);
            $this->taxable = true;
        } else {
            $this->taxable = false;
        }

        return $this;
    }

    public function setDefault()
    {
        $this->rate = app('api')->taxes()->getDefaultRecord();

        return $this;
    }

    public function get()
    {
        return $this->rate;
    }

    public function set($rate)
    {
        try {
            if (is_numeric($rate)) {
                $this->percent = $rate;
            } elseif ($rate instanceof Tax) {
                $this->percent = $rate->percentage;
            } else {
                $this->percent = app('api')->taxes()->getByName($rate)->percentage;
            }
        } catch (ModelNotFoundException $e) {
            $this->setDefault();
        }

        return $this;
    }

    public function amount($price)
    {
        if (! $this->percent) {
            return 0;
        }

        return $this->amountToAdd($price);
    }

    protected function amountToAdd($price)
    {
        if (! $this->taxable) {
            return 0;
        }

        $incVat = $price * $this->getVatMultiplier();
        $vatAmount = $incVat - $price;

        return $this->floorDec($vatAmount, 2);
    }

    /**
     * This will force a round down regardless of decimal
     */
    protected function floorDec($val, $precision = 2) {
        if ($precision < 0) { $precision = 0; }
        $numPointPosition = intval(strpos($val, '.'));
        if ($numPointPosition === 0) { //$val is an integer
            return $val;
        }
        return floatval(substr($val, 0, $numPointPosition + $precision + 1));
    }

    protected function getVatMultiplier()
    {
        return (($this->percent + 100) / 100);
    }

    public function add($price)
    {
        if (! $this->taxable) {
            return $price;
        }

        return $price + $this->amountToAdd($price);
    }
}
