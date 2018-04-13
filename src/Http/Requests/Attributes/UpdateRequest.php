<?php

namespace GetCandy\Api\Http\Requests\Attributes;

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
        $decodedId = app('api')->attributes()->getDecodedId($this->attribute);

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
