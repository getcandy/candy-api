<?php

namespace GetCandy\Api\Http\Requests\Tags;

use GetCandy\Api\Http\Requests\FormRequest;

class UpdateRequest extends FormRequest
{
    public function authorize()
    {
        return $this->user()->hasRole('admin');
    }

    public function rules()
    {
        $decodedId = app('api')->tags()->getDecodedId($this->tag);

        return [
            'name' => 'required|array|valid_locales',
        ];
    }
}
