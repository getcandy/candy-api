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
            'layout_id' => 'hashid_is_valid:layouts',
            'attribute_data' => 'array',
        ];

        $attributes = app('api')->products()->getAttributes($this->product);
        $defaultChannel = app('api')->channels()->getDefaultRecord();
        $defaultLanguage = app('api')->languages()->getDefaultRecord();

        foreach ($attributes as $attribute) {
            if ($attribute->required) {
                $rulestring = 'attribute_data.'.$attribute->handle.'.'.$defaultChannel->handle.'.'.$defaultLanguage->lang;
                // $ruleset[$rulestring] = 'required';
            }
        }

        return $ruleset;
    }

    public function messages()
    {
        return [
            'attribute_data.*.*.*.required' => 'This field is required',
        ];
    }
}
