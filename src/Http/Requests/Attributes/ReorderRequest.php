<?php

namespace GetCandy\Api\Http\Requests\Attributes;

use Auth;
use GetCandy\Http\Requests\FormRequest;
use GetCandy\Api\Attributes\Models\AttributeGroup;
use GetCandy\Api\Attributes\AttributeManager;

class ReorderRequest extends FormRequest
{
    public function authorize()
    {
        return $this->user()->can('create', AttributeGroup::class);
    }

    public function rules(AttributeManager $manager)
    {
        $count = $repo->count();
        return [
            'groups' => 'required|size:' . $count
        ];
    }

    public function messages()
    {
        return [
            'groups.size' => 'You must submit all groups'
        ];
    }
}
