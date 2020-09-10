<?php

namespace GetCandy\Api\Core\Currencies\Resources;

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
