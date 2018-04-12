<?php

namespace GetCandy\Api\Http\Requests\Users;

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
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $user = app('api')->users()->getDecodedId($this->user);
        return [
            'email' => 'required|unique:users,email,'. $user,
            'password' => 'confirmed|min:8'
        ];
    }
}
