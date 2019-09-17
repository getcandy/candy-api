<?php

namespace GetCandy\Api\Http\Resources\Baskets;

use GetCandy\Api\Http\Resources\AbstractResource;
use GetCandy\Api\Http\Resources\Users\UserResource;
use GetCandy\Api\Http\Resources\Orders\OrderResource;
use GetCandy\Api\Http\Resources\Discounts\DiscountCollection;

class BasketResource extends AbstractResource
{
    public function payload()
    {
        return [
            'id' => $this->encoded_id,
            'total' => $this->total_cost,
            'sub_total' => $this->sub_total,
            'tax_total' => $this->total_tax,
            'discount_total' => round($this->discount_total, 2),
            'changed' => $this->changed,
            'has_exclusions' => $this->hasExclusions,
            'meta' => $this->meta,
        ];
    }

    public function includes()
    {
        return [
            'lines' => new BasketLineCollection($this->whenLoaded('lines')),
            'user' => new UserResource($this->whenLoaded('user')),
            'discounts' => new DiscountCollection($this->whenLoaded('discounts')),
            'order' => ['data' => new OrderResource($this->whenLoaded('order'))],
        ];
    }
}
