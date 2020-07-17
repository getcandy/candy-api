<?php

namespace GetCandy\Api\Http\Requests\AttributeGroups;

use GetCandy;
use GetCandy\Api\Http\Requests\FormRequest;

class UpdateRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        // return $this->user()->can('update', AttributeGroup::class);
        return $this->user()->hasRole('admin');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $service = GetCandy::attributeGroups();

        return [
            'handle' => 'required|unique:attribute_groups,handle,'.$service->getDecodedId($this->attribute_group),
        ];
    }
}
