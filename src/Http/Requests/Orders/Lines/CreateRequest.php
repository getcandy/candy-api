<?php

namespace GetCandy\Api\Http\Requests\Orders\Lines;

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
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'quantity' => 'required|numeric|min:1',
            'line_total' => 'numeric|required_without:unit_price|min:0',
            'unit_price' => 'numeric|required_without:line_total|min:0',
            'tax_rate' => 'numeric|required|min:0',
            'description' => 'required',
        ];
    }
}
