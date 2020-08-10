<?php

namespace GetCandy\Api\Http\Resources\Countries;

use GetCandy\Api\Http\Resources\AbstractResource;

class CountryResource extends AbstractResource
{
    public function payload()
    {
        return [
            'id' => $this->encoded_id,
            'name' => $this->name,
            'region' => $this->region,
            'iso_a_2' => $this->iso_a_2,
            'iso_a_3' => $this->iso_a_3,
            'iso_numeric' => $this->iso_numeric,
        ];
    }
}
