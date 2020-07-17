<?php

namespace GetCandy\Api\Http\Requests\Baskets;

use GetCandy;
use GetCandy\Api\Http\Requests\FormRequest;

class DeleteRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        $basket = GetCandy::baskets()->getByHashedId($this->basket);
        $this->basket = $basket;

        return $basket->user->id == $this->user()->id;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [];
    }
}
