<?php

namespace GetCandy\Api\Http\Requests\AttributeGroups;

use GetCandy\Api\Http\Requests\FormRequest;
use GetCandy\Api\Attributes\Models\AttributeGroup;

class ReorderRequest extends FormRequest
{
    public function authorize()
    {
        // return $this->user()->can('create', AttributeGroup::class);
        return $this->user()->hasRole('admin');
    }

    public function rules()
    {
        $count = app('api')->attributeGroups()->count();

        return [
            'groups' => 'required|size:'.$count,
        ];
    }

    public function messages()
    {
        return [
            'groups.size' => 'You must submit all groups',
        ];
    }
}
