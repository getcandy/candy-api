<?php

namespace GetCandy\Api\Core\Currencies;

use Illuminate\Database\Eloquent\ModelNotFoundException;

class CurrencyConverter
{
    protected $currency;

    public function setDefault()
    {
        $this->currency = app('api')->currencies()->getDefaultRecord();

        return $this;
    }

    public function get()
    {
        return $this->currency;
    }

    public function rate()
    {
        return $this->currency->exchange_rate;
    }

    public function set($currency)
    {
        try {
            $this->currency = app('api')->currencies()->getByCode($currency);
        } catch (ModelNotFoundException $e) {
            $this->setDefault();
        }

        return $this;
    }

    public function format($price)
    {
        $formatted = number_format($price, 2, $this->currency->decimal_point, $this->currency->thousand_point);

        return str_replace('{price}', $formatted, $this->currency->format);
    }

    public function convert($price, $currency = null)
    {
        if (! $this->currency) {
            $this->set($currency);
        }

        return $price * $this->currency->exchange_rate;
    }
}
