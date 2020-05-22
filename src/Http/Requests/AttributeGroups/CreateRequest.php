<?php

namespace GetCandy\Api\Http\Requests\AttributeGroups;

use GetCandy\Api\Http\Requests\FormRequest;

class CreateRequest extends FormRequest
{
    public function authorize()
    {
        // return $this->user()->can('create', Attribute::class);
        return $this->user()->hasRole('admin');
    }

    public function rules()
    {
        return [
            'name' => 'required|array',
            'handle' => 'required|unique:attribute_groups,handle',
        ];
    }
}
