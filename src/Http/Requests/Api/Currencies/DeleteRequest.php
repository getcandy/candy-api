<?php

namespace GetCandy\Api\Http\Requests\Currencies;

use GetCandy\Api\Http\Requests\FormRequest;
use GetCandy\Api\Currencies\Models\Currency;

class DeleteRequest extends FormRequest
{
    public function authorize()
    {
        // return $this->user()->can('delete', Currency::class);
        return $this->user()->hasRole('admin');
    }

    public function rules()
    {
        return [
        ];
    }
}
