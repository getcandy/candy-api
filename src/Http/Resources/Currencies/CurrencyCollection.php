<?php

namespace GetCandy\Api\Http\Resources\Currencies;

use GetCandy\Api\Http\Resources\AbstractCollection;

class CurrencyCollection extends AbstractCollection
{
    /**
     * The resource that this resource collects.
     *
     * @var string
     */
    public $collects = CurrencyResource::class;
}
