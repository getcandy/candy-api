<?php

namespace GetCandy\Api\Http\Requests\ProductFamilies;

use GetCandy\Api\Http\Requests\FormRequest;

class DeleteRequest extends FormRequest
{
    public function authorize()
    {
        // return $this->user()->can('create', Language::class);
        return $this->user()->hasRole('admin');
    }

    public function rules()
    {
        return [
        ];
    }
}
