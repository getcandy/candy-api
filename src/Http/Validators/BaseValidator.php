<?php

namespace GetCandy\Api\Http\Validators;

use GetCandy\Api\Exceptions\InvalidServiceException;

class BaseValidator
{
    /**
     * Validates the name for an attribute doesn't exist in the same group.
     *
     * @param  string  $attribute
     * @param  string  $value
     * @param  array  $parameters
     * @param  \Illuminate\Contracts\Validation\Validator  $validator
     * @return bool
     */
    public function enabled($attribute, $value, $parameters, $validator)
    {
        if (empty($parameters[0])) {
            return false;
        }

        $method = str_plural($parameters[0]);

        try {
            $service = GetCandy::{$method}();

            return $service->getEnabled($value, isset($parameters[1]) ? $parameters[1] : null);
        } catch (InvalidServiceException $e) {
            return false;
        }
    }
}
