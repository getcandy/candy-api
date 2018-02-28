<?php
namespace GetCandy\Api\Payments\Validators;

class PaymentTokenValidator
{
    public function validate($attribute, $value, $parameters, $validator)
    {
        return app('api')->payments()->validateToken($value);
    }
}
