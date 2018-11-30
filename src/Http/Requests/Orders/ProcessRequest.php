<?php

namespace GetCandy\Api\Http\Requests\Orders;

use GetCandy\Api\Http\Requests\FormRequest;

class ProcessRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        // return $this->user()->can('create', Category::class);
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $rules = [
            'order_id' => 'required',
        ];

        if (! $this->force) {
            $rules['payment_token'] = 'required_without:payment_type_id';
            $rules['payment_type_id'] = 'required_without:payment_token|hashid_is_valid:payment_types';
        }

        return $rules;
    }
}
