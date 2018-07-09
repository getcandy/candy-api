<?php

namespace GetCandy\Api\Http\Transformers\Fractal\Baskets;

use GetCandy\Api\Core\Baskets\Models\SavedBasket;
use GetCandy\Api\Http\Transformers\Fractal\BaseTransformer;
use GetCandy\Api\Http\Transformers\Fractal\Baskets\BasketTransformer;

class SavedBasketTransformer extends BaseTransformer
{
    public $availableIncludes = [
        'basket'
    ];

    public function transform(SavedBasket $basket)
    {
        return [
            'id' => $basket->encodedId(),
            'name' => $basket->name
        ];
    }

    public function includeBasket(SavedBasket $basket)
    {
        return $this->item($basket->basket, new BasketTransformer);
    }
}
