<?php

namespace GetCandy\Api\Http\Resources\Customers;

use GetCandy\Api\Http\Resources\AbstractResource;

class CustomerGroupResource extends AbstractResource
{
    public function payload()
    {
        return [
            'id' => $this->encoded_id,
            'name' => $this->name,
            'handle' => $this->handle,
            'visible' => $this->pivot->visible ?? false,
            'purchasable' => $this->pivot->purchasable ?? false,
        ];
    }
}
