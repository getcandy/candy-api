<?php

namespace GetCandy\Api\Http\Requests\Attributes;

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
        $decodedId = GetCandy::attributes()->getDecodedId($this->attribute);

        return [
            'name' => 'required|array|valid_locales',
            'filterable' => 'boolean',
            'searchable' => 'boolean',
            'position' => 'integer',
            'variant' => 'boolean',
            'handle' => 'unique:attributes,handle,'.$decodedId,
        ];
    }
}
