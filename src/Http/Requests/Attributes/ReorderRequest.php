<?php

namespace GetCandy\Api\Http\Requests\Attributes;

use GetCandy\Api\Core\Attributes\Models\AttributeGroup;
use GetCandy\Api\Http\Requests\FormRequest;

class ReorderRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        // return $this->user()->can('create', AttributeGroup::class);
        return $this->user()->hasRole('admin');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'attributes' => 'required|array',
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array
     */
    public function messages()
    {
        return [
            'groups.size' => 'You must submit all groups',
        ];
    }
}
