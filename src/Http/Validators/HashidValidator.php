<?php

namespace GetCandy\Api\Http\Validators;

class HashidValidator
{
    /**
     * Determines whether a given hashid correctly decodes for the given model.
     * @param  string $attribute
     * @param  string $value
     * @param  array $parameters
     * @param  Validator $validator
     * @return bool
     */
    public function validForModel($attribute, $value, $parameters, $validator)
    {
        if (empty($parameters)) {
            abort(500, 'hashid_is_valid expects model reference');
        }

        $method = $parameters[0];

        // Have we passed the class reference through.

        if (class_exists($method)) {
            $result = (bool) (new $method)->decodeId($value);
        } else {
            $result = app('api')->{camel_case($method)}()->existsByHashedId($value);
        }

        if (is_array($value)) {
            return $result === count($value);
        }

        return $result;
    }
}
