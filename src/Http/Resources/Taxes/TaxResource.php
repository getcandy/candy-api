<?php

namespace GetCandy\Api\Http\Resources\Taxes;

use GetCandy\Api\Http\Resources\AbstractResource;

class TaxResource extends AbstractResource
{
    public function payload()
    {
        return [
            'id' => $this->encoded_id,
            'name' => $this->name,
            'percentage' => $this->percentage,
            'default' => (bool) $this->default,
        ];
    }
}
