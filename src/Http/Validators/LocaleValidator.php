<?php

namespace GetCandy\Api\Http\Validators;

class LocaleValidator
{
    /**
     * Validates the name for an attribute doesn't exist in the same group.
     * @param  string $attribute
     * @param  string $value
     * @param  array $parameters
     * @param  Validator $validator
     * @return bool
     */
    public function validate($attribute, $value, $parameters, $validator)
    {
        if (! is_array($value)) {
            return false;
        }
        $locales = array_keys($value);
        if (! app('api')->languages()->allLocalesExist($locales)) {
            return false;
        }

        return true;
    }
}
