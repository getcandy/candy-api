<?php

namespace GetCandy\Api\Core\Products\Validators;

class ProductValidator
{
    public function available($attribute, $value, $parameter, $validator)
    {
        $variant = app('api')->productVariants()->getByHashedId($value);

        if (! $variant) {
            return false;
        }

        $validator->addReplacer('available', function ($message, $attribute, $rule, $parameters) use ($variant) {
            return trans('getcandy::validation.available', [
                'sku' => $variant->sku,
            ]);
        });

        return $variant->availableProduct()->exists();
    }
}
