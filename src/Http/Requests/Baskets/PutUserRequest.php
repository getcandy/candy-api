<?php

namespace GetCandy\Api\Http\Requests\Baskets;

use GetCandy\Api\Http\Requests\FormRequest;

class PutUserRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        // return $this->user()->can('create', Category::class);
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'user_id' => 'required|hashid_is_valid:users',
        ];
    }
}
