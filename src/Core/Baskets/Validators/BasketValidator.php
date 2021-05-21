<?php

namespace GetCandy\Api\Core\Baskets\Validators;

use GetCandy;
use GetCandy\Api\Core\Baskets\Models\Basket;
use GetCandy\Api\Core\Products\Actions\CheckStock;

class BasketValidator
{
    public function uniqueLines($value, $parameters, $basketId, $validator)
    {
        $unique = collect($parameters)->unique('id');

        return $unique->count() == count($parameters);
    }

    public function inStock($attribute, $value, $parameters, $validator)
    {
        return CheckStock::run([
            'basket_id' => $parameters[1] ?? null,
            'quantity' => $value,
            'variant_id' => $parameters[0] ?? null
        ]);
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
