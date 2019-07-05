<?php

namespace GetCandy\Api\Http\Requests\Orders\Shipping;

use GetCandy\Api\Http\Requests\FormRequest;

class AddShippingRequest extends FormRequest
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
            'price_id' => 'required|hashid_is_valid:shipping_prices',
        ];
    }

    public function messages()
    {
        return [
            'price_id.required' => 'Please choose a shipping option',
        ];
    }
}
