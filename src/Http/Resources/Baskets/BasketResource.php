<?php

namespace GetCandy\Api\Http\Resources\Baskets;

use GetCandy\Api\Http\Resources\AbstractResource;
use GetCandy\Api\Http\Resources\Users\UserResource;
use GetCandy\Api\Http\Resources\Routes\RouteCollection;
use GetCandy\Api\Http\Resources\Orders\OrderCollection;
use GetCandy\Api\Http\Resources\Discounts\DiscountCollection;
use GetCandy\Api\Http\Resources\Orders\OrderResource;

class BasketResource extends AbstractResource
{
    public function payload()
    {
        return [
            'id' => $this->encoded_id,
            'total' => $this->total_cost,
            'sub_total' => $this->sub_total,
            'tax_total' => $this->total_tax,
            'discount_total' => $this->discount_total,
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