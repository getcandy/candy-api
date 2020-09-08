<?php

namespace GetCandy\Api\Core\Countries\Resources;

use GetCandy\Api\Http\Resources\AbstractResource;

class StateResource extends AbstractResource
{
    public function payload()
    {
        return [
            'id' => $this->encoded_id,
            'name' => $this->name,
            'code' => $this->code,
        ];
    }

    public function includes()
    {
        return [
            'country' => new CountryResource($this->whenLoaded('country')),
        ];
    }
}
