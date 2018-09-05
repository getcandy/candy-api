<?php

namespace GetCandy\Api\Http\Transformers\Fractal\Baskets;

use GetCandy\Api\Core\Baskets\Models\SavedBasket;
use GetCandy\Api\Core\Baskets\Factories\BasketFactory;
use GetCandy\Api\Http\Transformers\Fractal\BaseTransformer;

class SavedBasketTransformer extends BaseTransformer
{
    public $availableIncludes = [
        'basket',
    ];

    public function transform(SavedBasket $basket)
    {
        return [
            'id' => $basket->encodedId(),
            'name' => $basket->name,
        ];
    }

    public function includeBasket(SavedBasket $savedBasket)
    {
        $factory = app()->make(BasketFactory::class);

        return $this->item(
            $factory->init($savedBasket->basket)->get(),
            new BasketTransformer
        );
    }
}
