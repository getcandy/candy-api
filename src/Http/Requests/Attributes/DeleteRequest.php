<?php

namespace GetCandy\Api\Http\Requests\Attributes;

use GetCandy\Api\Core\Attributes\Models\Attribute;
use GetCandy\Api\Http\Requests\FormRequest;

class DeleteRequest extends FormRequest
{
    public function authorize()
    {
        // return $this->user()->can('delete', Attribute::class);
        return $this->user()->hasRole('admin');
    }

    public function rules()
    {
        return [
        ];
    }
}
