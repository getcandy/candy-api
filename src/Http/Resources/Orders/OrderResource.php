<?php

namespace GetCandy\Api\Http\Resources\Orders;

use GetCandy\Api\Http\Resources\AbstractResource;

class OrderResource extends AbstractResource
{
    public function payload()
    {
        return [
            'id' => $this->encoded_id,
        ];
    }
}