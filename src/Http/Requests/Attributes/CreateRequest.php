<?php

namespace GetCandy\Api\Http\Requests\Attributes;

use GetCandy\Api\Http\Requests\FormRequest;
use GetCandy\Api\Core\Attributes\Models\Attribute;

class CreateRequest extends FormRequest
{
    public function authorize()
    {
        // return $this->user()->can('create', Attribute::class);
        return $this->user()->hasRole('admin');
    }

    public function rules(Attribute $attribute)
    {
        return [
            'group_id' => 'required',
            'name' => 'array|required|valid_locales',
            'handle' => 'required|unique:attributes,handle',
        ];
    }
}
