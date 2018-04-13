<?php

namespace GetCandy\Api\Http\Requests\Tags;

use GetCandy\Api\Http\Requests\FormRequest;
use GetCandy\Api\Tags\Models\Tag;

class CreateRequest extends FormRequest
{
    public function authorize()
    {
        return $this->user()->hasRole('admin');
    }

    public function rules(Tag $tag)
    {
        return [
            'name' => 'array|required|valid_locales',
        ];
    }
}
