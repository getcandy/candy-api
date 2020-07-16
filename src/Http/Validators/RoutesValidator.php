<?php

namespace GetCandy\Api\Http\Validators;

use GetCandy;

class RoutesValidator
{
    /**
     * Validates the slug for a route.
     *
     * @param  string  $attribute
     * @param  string  $value
     * @param  array  $parameters
     * @param  \Illuminate\Contracts\Validation\Validator  $validator
     * @return bool
     */
    public function uniqueRoute($attribute, $value, $parameters, $validator)
    {
        return GetCandy::routes()->uniqueSlug($value, $parameters[0]);
    }
}
