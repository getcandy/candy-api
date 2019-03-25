<?php

namespace GetCandy\Api\Core\Baskets\Validators;

class BasketValidator
{
    public function uniqueLines($value, $parameters, $basketId, $validator)
    {
        $unique = collect($parameters)->unique('id');

        return $unique->count() == count($parameters);
    }

    public function inStock($attribute, $value, $parameters, $validator)
    {
        return app('api')->productVariants()->canAddToBasket($parameters[0] ?? null, $value);
    }

    public function minQuantity($attribute, $value, $parameters, $validator)
    {
        return $value >= ($parameters[0] ?? 1);
    }

    public function minBatch($attribute, $value, $parameters, $validator)
    {
        return ($value % $parameters[0] ?? 1) === 0;
    }
}
