<?php

namespace GetCandy\Api\Http\Requests\AttributeGroups;

use GetCandy\Api\Http\Requests\FormRequest;

class UpdateRequest extends FormRequest
{
    public function authorize()
    {
        // return $this->user()->can('update', AttributeGroup::class);
        return $this->user()->hasRole('admin');
    }

    public function rules()
    {
        $service = app('api')->attributeGroups();

        return [
            'handle' => 'required|unique:attribute_groups,handle,'.$service->getDecodedId($this->attribute_group),
        ];
    }
}
