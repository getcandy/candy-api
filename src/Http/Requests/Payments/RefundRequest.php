<?php

namespace GetCandy\Api\Http\Requests\Payments;

use GetCandy;
use GetCandy\Api\Http\Requests\FormRequest;

class RefundRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        // $this->transaction = GetCandy::payments()->getByHashedId($this->id);
        return $this->user()->hasRole('admin');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'amount' => 'required|numeric|min:1',
        ];
    }
}
