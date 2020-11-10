<?php

namespace GetCandy\Api\Core\Customers\Resources;

use GetCandy\Api\Http\Resources\AbstractResource;

class CustomerInviteResource extends AbstractResource
{
    public function payload()
    {
        return [
            'id' => $this->encoded_id,
            'email' => $this->email,
        ];
    }

    public function includes()
    {
        return [
            'customer' => new CustomerResource($this->whenLoaded('customer')),
        ];
    }
}
