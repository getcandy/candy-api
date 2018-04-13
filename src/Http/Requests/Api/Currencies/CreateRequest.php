<?php

namespace GetCandy\Api\Http\Requests\Currencies;

use GetCandy\Api\Http\Requests\FormRequest;
use GetCandy\Api\Currencies\Models\Currency;

class CreateRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        // return $this->user()->can('create', Currency::class);
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
            'name' => 'required',
            'code' => 'required|unique:currencies,code',
            'enabled' => 'required',
            'exchange_rate' => 'required',
            'format' => 'required',
        ];
    }
}
