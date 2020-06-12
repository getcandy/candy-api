<?php

namespace GetCandy\Api\Http\Requests\Currencies;

use GetCandy\Api\Currencies\Models\Currency;
use GetCandy\Api\Http\Requests\FormRequest;

class UpdateRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        // return $this->user()->can('update', Currency::class);
        return $this->user()->hasRole('admin');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(Currency $currency)
    {
        return [
            'code' => 'unique:currencies,code,'.$currency->decodeId($this->currency),
        ];
    }
}
