<?php

namespace GetCandy\Api\Http\Transformers\Fractal\Currencies;

use GetCandy\Api\Core\Currencies\Models\Currency;
use GetCandy\Api\Http\Transformers\Fractal\BaseTransformer;

class CurrencyTransformer extends BaseTransformer
{
    /**
     * Resources that can be included if requested.
     *
     * @var array
     */
    protected $availableIncludes = [];

    public function transform(Currency $currency)
    {
        return [
            'id' => $currency->encodedId(),
            'name' => $currency->name,
            'code' => $currency->code,
            'format' => $currency->format,
            'decimal' => $currency->decimal_point,
            'thousand' => $currency->thousand_point,
            'exchange_rate' => $currency->exchange_rate,
            'enabled' => (bool) $currency->enabled,
            'default' => (bool) $currency->default,
        ];
    }
}
