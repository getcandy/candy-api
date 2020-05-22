<?php

namespace GetCandy\Api\Http\Requests\Attributes;

use GetCandy\Api\Attributes\Models\AttributeGroup;
use GetCandy\Api\Http\Requests\FormRequest;

class ReorderRequest extends FormRequest
{
    public function authorize()
    {
        // return $this->user()->can('create', AttributeGroup::class);
        return $this->user()->hasRole('admin');
    }

    public function rules()
    {
        return [
            'attributes' => 'required|array',
        ];
    }

    public function messages()
    {
        return [
            'groups.size' => 'You must submit all groups',
        ];
    }
}
