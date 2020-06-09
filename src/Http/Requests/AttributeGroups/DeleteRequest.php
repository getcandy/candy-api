<?php

namespace GetCandy\Api\Http\Requests\AttributeGroups;

use GetCandy\Api\Http\Requests\FormRequest;

class DeleteRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        // return $this->user()->can('create', Attribute::class);
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
            'delete_attributes' => 'required_without:group_id',
            'group_id' => 'required_without:delete_attributes',
        ];
    }
}
