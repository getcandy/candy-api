<?php

namespace GetCandy\Api\Http\Requests\Payments;

use GetCandy\Api\Http\Requests\FormRequest;

class ValidateThreeDRequest extends FormRequest
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
            'paRes' => 'required',
            'transaction' => 'required',
            'order_id' => 'required|hashid_is_valid:orders',
        ];
    }
}
