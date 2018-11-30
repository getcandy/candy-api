<?php

namespace GetCandy\Api\Http\Resources\Discounts;

use GetCandy\Api\Http\Resources\AbstractResource;

class DiscountResource extends AbstractResource
{
    public function payload()
    {
        return [
            'id' => $this->encoded_id,
        ];
    }
}