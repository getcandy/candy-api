<?php

namespace GetCandy\Api\Http\Resources\Countries;

use GetCandy\Api\Http\Resources\AbstractResource;

class CountryGroupResource extends AbstractResource
{
    public function payload()
    {
        return [
            'region' => $this->resource->first()->region ?: 'Rest of world',
        ];
    }
}
