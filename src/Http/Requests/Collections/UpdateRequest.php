<?php

namespace GetCandy\Api\Http\Requests\Collections;

use GetCandy\Api\Http\Requests\FormRequest;

class UpdateRequest extends FormRequest
{
    public function authorize()
    {
        return $this->user()->hasRole('admin');
    }
    public function rules()
    {
        return [
            'attributes' => 'required|array'
        ];
    }
}
