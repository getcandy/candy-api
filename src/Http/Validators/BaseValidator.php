<?php

namespace GetCandy\Api\Http\Validators;

use InvalidArgumentException;

class BaseValidator
{
    /**
     * Validates the name for an attribute doesn't exist in the same group
     * @param  String $attribute
     * @param  String $value
     * @param  Array $parameters
     * @param  Validator $validator
     * @return Bool
     */
    public function enabled($attribute, $value, $parameters, $validator)
    {
        if (empty($parameters[0])) {
            return false;
        }

        $method = str_plural($parameters[0]);

        if (!property_exists(app('api'), $method)) {
            return false;
        }

        return app('api')->{$method}()->getEnabled($value, isset($parameters[1]) ? $parameters[1] : null);
    }
}
