<?php

namespace GetCandy\Api\Http\Transformers\Fractal\Baskets;

use GetCandy\Api\Core\Baskets\Models\Basket;
use GetCandy\Api\Http\Transformers\Fractal\BaseTransformer;
use GetCandy\Api\Http\Transformers\Fractal\Users\UserTransformer;
use GetCandy\Api\Http\Transformers\Fractal\Orders\OrderTransformer;
use GetCandy\Api\Http\Transformers\Fractal\Routes\RouteTransformer;
use GetCandy\Api\Http\Transformers\Fractal\Discounts\DiscountTransformer;

class BasketTransformer extends BaseTransformer
{
    protected $availableIncludes = [
        'lines', 'user', 'discounts', 'routes', 'order',
    ];

    public function transform(Basket $basket)
    {
        $data = array_merge($basket->custom_attributes, [
            'id' => $basket->encodedId(),
            'total' => round($basket->total_cost, 2),
            'sub_total' => $basket->sub_total,
            'tax_total' => $basket->total_tax,
            'discount_total' => $basket->discount_total,
        ]);

        return $data;
    }

    protected function includeLines(Basket $basket)
    {
        return $this->collection($basket->lines, new BasketLineTransformer);
    }

    protected function includeOrder(Basket $basket)
    {
        if (! $basket->order) {
            return $this->null();
        }

        return $this->item($basket->order, new OrderTransformer);
    }

    public function includeRoutes(Basket $basket)
    {
        return $this->collection($basket->routes, new RouteTransformer);
    }

    protected function includeUser(Basket $basket)
    {
        if (! $basket->user) {
            return;
        }

        return $this->item($basket->user, new UserTransformer);
    }

    protected function includeDiscounts(Basket $basket)
    {
        return $this->collection($basket->discounts, new DiscountTransformer);
    }
}
