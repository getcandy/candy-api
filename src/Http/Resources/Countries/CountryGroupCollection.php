<?php

namespace GetCandy\Api\Http\Resources\Countries;

use GetCandy\Api\Http\Resources\AbstractCollection;

class CountryGroupCollection extends AbstractCollection
{
    /**
     * The resource that this resource collects.
     *
     * @var string
     */
    public $collects = CountryGroupResource::class;
}
