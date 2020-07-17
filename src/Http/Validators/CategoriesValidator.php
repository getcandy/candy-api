<?php

namespace GetCandy\Api\Http\Validators;

use GetCandy;

class CategoriesValidator
{
    /**
     * Validates the name for an attribute doesn't exist in the same group.
     *
     * @param  string  $attribute
     * @param  string  $value
     * @param  array  $key
     * @param  \Illuminate\Contracts\Validation\Validator  $validator
     * @return bool
     */
    public function uniqueCategoryAttributeData($attribute, $value, $key, $validator)
    {
        return GetCandy::categories()->uniqueAttribute($key[0], $value);
    }
}
