<?php

namespace GetCandy\Api\Http\Requests\Currencies;

use GetCandy\Api\Http\Requests\FormRequest;
use GetCandy\Api\Currencies\Models\Currency;

class UpdateRequest extends FormRequest
{
    public function authorize()
    {
        // return $this->user()->can('update', Currency::class);
        return $this->user()->hasRole('admin');
    }

    public function rules(Currency $currency)
    {
        return [
            'code' => 'unique:currencies,code,'.$currency->decodeId($this->currency),
        ];
    }
}
