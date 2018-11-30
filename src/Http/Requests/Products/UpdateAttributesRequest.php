<?php

namespace GetCandy\Api\Http\Requests\Products;

use GetCandy\Api\Http\Requests\FormRequest;

class UpdateAttributesRequest extends FormRequest
{
    public function authorize()
    {
        return $this->user()->hasRole('admin');
    }

    public function rules()
    {
        return [
            'attributes' => 'required|array',
        ];
    }

    public function messages()
    {
        return [
            'attributes' => 'This field is required',
        ];
    }
}
