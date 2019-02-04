<?php

namespace GetCandy\Api\Http\Resources\Discounts;

use GetCandy\Api\Http\Resources\AbstractResource;

class DiscountCriteriaResource extends AbstractResource
{
    public function payload()
    {
        return [
            'id' => $this->encoded_id,
        ];
    }

    public function includes()
    {
        return [
            'set' => ['data' => new DiscountSetResource($this->whenLoaded('set'))],
        ];
    }
}
