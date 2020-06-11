<?php

namespace GetCandy\Api\Core\Currencies;

use GetCandy\Api\Core\Currencies\Interfaces\CurrencyConverterInterface;
use GetCandy\Api\Core\Currencies\Interfaces\CurrencyServiceInterface;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class CurrencyConverter implements CurrencyConverterInterface
{
    /**
     * @var null|\GetCandy\Api\Core\Currencies\Models\Currency
     */
    protected $currency;

    /**
     * @var \GetCandy\Api\Core\Currencies\Interfaces\CurrencyServiceInterface
     */
    protected $currencies;

    public function __construct(CurrencyServiceInterface $currencies)
    {
        $this->currencies = $currencies;
    }

    public function setDefault()
    {
        $this->currency = $this->currencies->getDefaultRecord();

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
            $this->currency = $this->currencies->getByCode($currency);
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
