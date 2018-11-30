<?php

namespace GetCandy\Api\Http\Validators;

class CategoriesValidator
{
    /**
     * Validates the name for an attribute doesn't exist in the same group.
     * @param  string $attribute
     * @param  string $value
     * @return bool
     */
    public function uniqueCategoryAttributeData($attribute, $value, $key, $validator)
    {
        return app('api')->categories()->uniqueAttribute($key[0], $value);
    }
}
