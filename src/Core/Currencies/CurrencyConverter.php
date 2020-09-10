<?php

namespace GetCandy\Api\Core\Currencies;

use GetCandy\Api\Core\Currencies\Actions\FetchCurrency;
use GetCandy\Api\Core\Currencies\Actions\FetchDefaultCurrency;
use GetCandy\Api\Core\Currencies\Interfaces\CurrencyConverterInterface;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class CurrencyConverter implements CurrencyConverterInterface
{
    /**
     * @var null|\GetCandy\Api\Core\Currencies\Models\Currency
     */
    protected $currency;

    public function setDefault()
    {
        $this->currency = FetchDefaultCurrency::run();

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
            $this->currency = FetchCurrency::run([
                'search' => [
                    'code' => $currency,
                ],
            ]);
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
