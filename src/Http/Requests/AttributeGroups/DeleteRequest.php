<?php

namespace GetCandy\Api\Http\Requests\AttributeGroups;

use GetCandy\Api\Http\Requests\FormRequest;

class DeleteRequest extends FormRequest
{
    public function authorize()
    {
        // return $this->user()->can('create', Attribute::class);
        return $this->user()->hasRole('admin');
    }

    public function rules()
    {
        return [
            'delete_attributes' => 'required_without:group_id',
            'group_id' => 'required_without:delete_attributes',
        ];
    }
}
