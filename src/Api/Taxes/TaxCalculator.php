<?php

namespace GetCandy\Api\Taxes;

use Illuminate\Database\Eloquent\ModelNotFoundException;
use GetCandy\Api\Taxes\Models\Tax;

class TaxCalculator
{
    protected $rate;

    protected $taxable = false;

    protected $percent = 0;

    public function setTax($type)
    {
        $this->set($type);
        $this->taxable = true;
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
            if (!is_object($rate) && $rate == 0) {
                $this->percent = 0;
            } elseif (is_numeric($rate)) {
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
        if (!$this->percent) {
            return 0;
        }
        return $this->amountToAdd($price);
    }

    protected function amountToAdd($price)
    {
        if (!$this->taxable) {
            return 0;
        }
        $exVat = $price * (($this->percent + 100) / 100);
        $amount =  $exVat - $price;
        return $amount;
    }

    public function add($price)
    {
        if (!$this->taxable) {
            return $price;
        }
        return $price + $this->amountToAdd($price);
    }
}
