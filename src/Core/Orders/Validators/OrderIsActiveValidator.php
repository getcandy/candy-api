<?php

namespace GetCandy\Api\Core\Orders\Validators;

class OrderIsActiveValidator
{
    public function validate($attribute, $value, $parameters, $validator)
    {
        return app('api')->orders()->isActive($value);
    }
}
