<?php

namespace GetCandy\Api\Http\Resources\Payments;

use GetCandy\Api\Http\Resources\AbstractResource;

class PaymentTypeResource extends AbstractResource
{
    public function payload()
    {
        return [
            'id' => $this->encoded_id,
            'name' => $this->name,
            'handle' => $this->handle,
            'driver' => $this->driver,
        ];
    }
}
