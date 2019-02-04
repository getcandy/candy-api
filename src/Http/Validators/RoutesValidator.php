<?php

namespace GetCandy\Api\Http\Validators;

class RoutesValidator
{
    /**
     * Validates the slug for a route.
     * @param  string $attribute
     * @param  string $value
     * @return bool
     */
    public function uniqueRoute($attribute, $value, $parameters, $validator)
    {
        return app('api')->routes()->uniqueSlug($value, $parameters[0]);
    }
}
