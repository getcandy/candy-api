<?php

namespace GetCandy\Api\Core\Orders\Validators;

use GetCandy;

class OrderIsActiveValidator
{
    public function validate($attribute, $value, $parameters, $validator)
    {
        return GetCandy::orders()->isActive($value);
    }
}
