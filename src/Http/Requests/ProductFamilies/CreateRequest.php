<?php

namespace GetCandy\Api\Http\Requests\ProductFamilies;

use GetCandy\Api\Http\Requests\FormRequest;

class CreateRequest extends FormRequest
{
    public function authorize()
    {
        // return $this->user()->can('create', Language::class);
        return $this->user()->hasRole('admin');
    }

    public function rules()
    {
        return [
            'name' => 'array|required|valid_structure:product_families',
        ];
    }
}
