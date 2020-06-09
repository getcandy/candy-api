<?php

namespace GetCandy\Api\Http\Requests\Attributes;

use GetCandy\Api\Core\Attributes\Models\Attribute;
use GetCandy\Api\Http\Requests\FormRequest;

class CreateRequest extends FormRequest
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
    public function rules(Attribute $attribute)
    {
        return [
            'group_id' => 'required',
            'name' => 'array|required|valid_locales',
            'handle' => 'required|unique:attributes,handle',
        ];
    }
}
