<?php

namespace GetCandy\Api\Core\Taxes;

use GetCandy\Api\Core\Taxes\Models\Tax;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class TaxCalculator
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
        $exVat = $price * (($this->percent + 100) / 100);
        $amount = $exVat - $price;

        return round($amount, 2);
    }

    public function add($price)
    {
        if (! $this->taxable) {
            return $price;
        }

        return $price + $this->amountToAdd($price);
    }
}
