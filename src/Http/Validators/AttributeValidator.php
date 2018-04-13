<?php

namespace GetCandy\Api\Http\Validators;

class AttributeValidator
{
    /**
     * Validates the name for an attribute doesn't exist in the same group.
     * @param  string $attribute
     * @param  string $value
     * @param  array $parameters
     * @param  Validator $validator
     * @return bool
     */
    public function uniqueNameInGroup($attribute, $value, $parameters, $validator)
    {
        if (empty($parameters[0])) {
            return false;
        }
        $attributeId = empty($parameters[1]) ? null : $parameters[1];

        return app('api')->attributes()->nameExistsInGroup($value, $parameters[0], $attributeId);
    }

    public function validateData($attribute, $value, $parameters, $validator)
    {
        if (! is_array($value) || empty($parameters[0])) {
            return false;
        }
        $classname = camel_case($parameters[0]);

        return app('api')->{$classname}()->validateAttributeData($value);
    }
}
