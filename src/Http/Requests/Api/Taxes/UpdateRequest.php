<?php

namespace GetCandy\Api\Http\Requests\Taxes;

use GetCandy\Api\Http\Requests\FormRequest;
use GetCandy\Api\Taxes\Models\Tax;

class UpdateRequest extends FormRequest
{
    public function authorize()
    {
        // return $this->user()->can('update', Tax::class);
        return $this->user()->hasRole('admin');
    }
    public function rules(Tax $tax)
    {
        return [
            'name' => 'unique:taxes,name,'. $tax->decodeId($this->tax)
        ];
    }
}
