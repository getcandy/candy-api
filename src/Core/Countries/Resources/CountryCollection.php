<?php

namespace GetCandy\Api\Core\Countries\Resources;

use GetCandy\Api\Http\Resources\AbstractCollection;

class CountryCollection extends AbstractCollection
{
    /**
     * The resource that this resource collects.
     *
     * @var string
     */
    public $collects = CountryResource::class;
}
