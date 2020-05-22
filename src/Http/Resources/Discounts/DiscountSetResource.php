<?php

namespace GetCandy\Api\Http\Resources\Discounts;

use GetCandy\Api\Http\Resources\AbstractResource;

class DiscountSetResource extends AbstractResource
{
    public function payload()
    {
        return [
            'id' => $this->encoded_id,
            'scope' => $this->scope,
            'outcome' => $this->outcome,
        ];
    }

    public function includes()
    {
        return [
            'discount' => ['data' => new DiscountResource($this->whenLoaded('discount'))],
            'items' => new DiscountItemCollection($this->whenLoaded('items')),
        ];
    }
}
