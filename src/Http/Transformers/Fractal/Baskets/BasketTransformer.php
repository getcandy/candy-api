<?php

namespace GetCandy\Api\Http\Transformers\Fractal\Baskets;

use GetCandy\Api\Baskets\Models\Basket;
use GetCandy\Api\Http\Transformers\Fractal\BaseTransformer;
use GetCandy\Api\Http\Transformers\Fractal\Discounts\DiscountTransformer;
use GetCandy\Api\Http\Transformers\Fractal\Routes\RouteTransformer;
use GetCandy\Api\Http\Transformers\Fractal\Users\UserTransformer;

class BasketTransformer extends BaseTransformer
{
    protected $availableIncludes = [
        'lines', 'user', 'discounts', 'routes',
    ];

    public function transform(Basket $basket)
    {
        $basket = app('api')->baskets()->setTotals($basket);
        $data = [
            'id'        => $basket->encodedId(),
            'total'     => round($basket->total, 2),
            'sub_total' => round($basket->sub_total, 2),
            'tax_total' => round($basket->tax, 2),
        ];

        return $data;
    }

    protected function includeLines(Basket $basket)
    {
        return $this->collection($basket->lines, new BasketLineTransformer());
    }

    public function includeRoutes(Basket $basket)
    {
        return $this->collection($basket->routes, new RouteTransformer());
    }

    protected function includeUser(Basket $basket)
    {
        if (!$basket->user) {
            return;
        }

        return $this->item($basket->user, new UserTransformer());
    }

    protected function includeDiscounts(Basket $basket)
    {
        return $this->collection($basket->discounts, new DiscountTransformer());
    }
}
