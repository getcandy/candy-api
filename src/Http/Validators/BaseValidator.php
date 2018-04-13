<?php

namespace GetCandy\Api\Http\Validators;

class BaseValidator
{
    /**
     * Validates the name for an attribute doesn't exist in the same group.
     * @param  string $attribute
     * @param  string $value
     * @param  array $parameters
     * @param  Validator $validator
     * @return bool
     */
    public function enabled($attribute, $value, $parameters, $validator)
    {
        if (empty($parameters[0])) {
            return false;
        }

        $method = str_plural($parameters[0]);

        if (! property_exists(app('api'), $method)) {
            return false;
        }

        return app('api')->{$method}()->getEnabled($value, isset($parameters[1]) ? $parameters[1] : null);
    }
}
