<?php

namespace GetCandy\Api\Core\Baskets\Validators;

use GetCandy\Api\Core\Products\Actions\CheckStock;
use GetCandy\Api\Core\Products\Actions\FetchStock;

class BasketValidator
{
    public function uniqueLines($value, $parameters, $basketId, $validator)
    {
        $unique = collect($parameters)->unique('id');

        return $unique->count() == count($parameters);
    }

    public function inStock($attribute, $value, $parameters, $validator)
    {
        $variantId = $parameters[0] ?? null;

        $validator->addReplacer('in_stock', function ($message, $attribute, $rule, $parameters) use ($variantId) {
            return trans('getcandy::validation.in_stock', [
                'stock' => FetchStock::run([
                    'variant_id' => $variantId,
                ]),
            ]);
        });

        return CheckStock::run([
            'basket_id' => $parameters[1] ?? null,
            'quantity' => $value,
            'variant_id' => $variantId,
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
