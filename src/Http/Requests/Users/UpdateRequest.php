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
        $user = app('api')->users()->getDecodedId($this->user);

        return $this->user()->hasRole('admin') || $this->user()->id == $user;
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
            'email' => 'required|unique:users,email,'.$user,
            'password' => 'confirmed|min:8',
        ];
    }
}
