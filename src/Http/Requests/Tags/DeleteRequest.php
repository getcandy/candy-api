<?php

namespace GetCandy\Api\Http\Requests\Tags;

use GetCandy\Api\Http\Requests\FormRequest;

class DeleteRequest extends FormRequest
{
    public function authorize()
    {
        return $this->user()->hasRole('admin');
    }

    public function rules()
    {
        return [];
    }
}
