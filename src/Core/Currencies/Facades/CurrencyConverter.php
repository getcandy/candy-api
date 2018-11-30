<?php

namespace GetCandy\Api\Core\Currencies\Facades;

use Illuminate\Support\Facades\Facade;

class CurrencyConverter extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'currency_converter';
    }
}
