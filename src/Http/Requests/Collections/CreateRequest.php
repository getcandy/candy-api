<?php

namespace GetCandy\Api\Http\Requests\Collections;

use GetCandy\Api\Http\Requests\FormRequest;

class CreateRequest extends FormRequest
{
    public function authorize()
    {
        return $this->user()->hasRole('admin');
    }

    public function rules()
    {
        return [
            'name' => 'required|valid_structure:collections',
            'url' => 'required',
        ];
    }
}
