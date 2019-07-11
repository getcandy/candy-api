<?php

namespace GetCandy\Api\Http\Requests\Baskets;

use GetCandy\Api\Http\Requests\FormRequest;

class DeleteLinesRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        foreach ($this->lines as $line) {
            $basket = app('api')->basketLines()->getByHashedId($line['id'])->basket;

            if ($basket->user->id !== $this->user()->id) {
                return false;
            }
        }

        return true;
    }

    public function rules()
    {
        return [
            'lines' => 'required|array|unique_lines',
            'lines.*.id' => 'required|hashid_is_valid:basket_lines',
        ];
    }
}
