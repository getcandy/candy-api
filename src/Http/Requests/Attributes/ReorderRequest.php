<?php

namespace GetCandy\Api\Http\Requests\Attributes;

use GetCandy\Http\Requests\FormRequest;
use GetCandy\Api\Attributes\AttributeManager;
use GetCandy\Api\Attributes\Models\AttributeGroup;

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
