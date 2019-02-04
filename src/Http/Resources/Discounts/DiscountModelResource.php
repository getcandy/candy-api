<?php

namespace GetCandy\Api\Http\Resources\Discounts;

use GetCandy\Api\Http\Resources\AbstractResource;

class DiscountModelResource extends AbstractResource
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
            'criteria' => ['data' => new DiscountCriteriaResource($this->whenLoaded('criteria'))],
        ];
    }
}
