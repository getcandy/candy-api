<?php

namespace GetCandy\Api\Http\Requests\Categories;

use GetCandy\Api\Http\Requests\FormRequest;
use GetCandy\Api\Categories\Models\Category;

class ReorderRequest extends FormRequest
{
    public function authorize()
    {
        return $this->user()->hasRole('admin');
    }

    public function rules()
    {
        return [
            'node'          => 'required',
            'moved-node'    => 'required',
            'action'        => 'required'
        ];
    }
}
