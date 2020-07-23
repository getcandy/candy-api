<?php

namespace GetCandy\Api\Http\Resources\Taxes;

use GetCandy\Api\Http\Resources\AbstractCollection;

class TaxCollection extends AbstractCollection
{
    /**
     * The resource that this resource collects.
     *
     * @var string
     */
    public $collects = TaxResource::class;
}
