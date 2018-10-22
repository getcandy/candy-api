<?php

namespace GetCandy\Api\Core\Baskets\Validators;

class BasketValidator
{
    public function uniqueLines($value, $parameters, $basketId, $validator)
    {
        $unique = collect($parameters)->unique('id');

        return $unique->count() == count($parameters);
    }

    public function inStock($value, $variant, $basketId, $validator)
    {
        return app('api')->productVariants()->canAddToBasket($variant['id'], $variant['quantity'] ?? null);
    }
}
