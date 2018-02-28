<?php

namespace GetCandy\Api\Baskets\Validators;

class BasketValidator
{
    public function uniqueLines($value, $parameters, $basketId, $validator)
    {
        $unique = collect($parameters)->unique('id');
        return $unique->count() == count($parameters);
    }
}
