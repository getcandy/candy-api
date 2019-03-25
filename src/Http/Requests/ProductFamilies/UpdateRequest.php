<?php

namespace GetCandy\Api\Http\Requests\ProductFamilies;

use GetCandy\Api\Http\Requests\FormRequest;

class UpdateRequest extends FormRequest
{
    public function authorize()
    {
        // return $this->user()->can('create', ProductFamily::class);
        return $this->user()->hasRole('admin');
    }

    public function rules()
    {
        return [
            'name' => 'array|required',
        ];
    }
}
