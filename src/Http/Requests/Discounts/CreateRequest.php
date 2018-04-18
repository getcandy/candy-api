<?php

namespace GetCandy\Api\Http\Requests\Discounts;

use GetCandy\Api\Http\Requests\FormRequest;

class CreateRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return $this->user()->hasRole('admin');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            // 'sets' => 'valid_discount',
            // 'name' => 'required',
            // 'result' => 'required',
            // 'value' => 'required'
        ];
    }

    public function messages()
    {
        return [
            'valid_discount' => 'You have duplicate items in your criteria',
        ];
    }
}
