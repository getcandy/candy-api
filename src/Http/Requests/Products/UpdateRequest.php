<?php

namespace GetCandy\Api\Http\Requests\Products;

use GetCandy\Api\Http\Requests\FormRequest;

class UpdateRequest extends FormRequest
{
    public function authorize()
    {
        return $this->user()->hasRole('admin');
    }

    public function rules()
    {
        $ruleset = [
            'family_id' => 'hashid_is_valid:product_families',
            'attribute_data' => 'required|array',
        ];

        $attributes = app('api')->products()->getAttributes($this->product);
        $defaultChannel = app('api')->channels()->getDefaultRecord();
        $defaultLanguage = app('api')->languages()->getDefaultRecord();

        foreach ($attributes as $attribute) {
            if ($attribute->required) {
                $rulestring = 'attribute_data.'.$attribute->handle.'.'.$defaultChannel->handle.'.'.$defaultLanguage->lang;
                $ruleset[$rulestring] = 'required';
            }
        }

        return $ruleset;
    }

    public function messages()
    {
        return [
            'attributes.*.*.*.required' => 'This Field is required',
        ];
    }
}
