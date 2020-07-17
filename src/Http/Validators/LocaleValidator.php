<?php

namespace GetCandy\Api\Http\Validators;

use GetCandy;

class LocaleValidator
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
    public function validate($attribute, $value, $parameters, $validator)
    {
        if (! is_array($value)) {
            return false;
        }
        $locales = array_keys($value);
        if (! GetCandy::languages()->allLocalesExist($locales)) {
            return false;
        }

        return true;
    }
}
