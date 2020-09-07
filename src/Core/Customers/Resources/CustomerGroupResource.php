<?php

namespace GetCandy\Api\Core\Customers\Resources;

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

    public function includes()
    {
        return [
            'customers' => new CustomerCollection($this->whenLoaded('customers')),
        ];
    }
}
