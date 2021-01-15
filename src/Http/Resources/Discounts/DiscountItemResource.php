<?php

namespace GetCandy\Api\Http\Resources\Discounts;

use GetCandy\Api\Core\Users\Resources\UserCollection;
use GetCandy\Api\Http\Resources\AbstractResource;

class DiscountItemResource extends AbstractResource
{
    public function payload()
    {
        return [
            'id' => $this->encoded_id,
            'type' => $this->type,
            'value' => $this->value,
        ];
    }

    public function includes()
    {
        return [
            'users' => new UserCollection($this->whenLoaded('users')),
        ];
    }
}
