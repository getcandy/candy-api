<?php

namespace GetCandy\Api\Http\Requests\Tags;

use GetCandy\Api\Core\Tags\Models\Tag;
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
        return $this->user()->hasRole('admin');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(Tag $tag)
    {
        return [
            'name' => 'array|required|valid_locales',
        ];
    }
}
