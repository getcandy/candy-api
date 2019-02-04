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

    public function includes()
    {
        return [
            'items' => new DiscountItemCollection($this->whenLoaded('items')),
            'rewards' => new DiscountRewardCollection($this->whenLoaded('rewards')),
        ];
    }
}
