<?php

namespace GetCandy\Api\Http\Requests\Auth;

use GetCandy\Api\Http\Requests\FormRequest;

class ImpersonateRequest extends FormRequest
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
    public function rules()
    {
        return [
            'customer_id' => 'required|hashid_is_valid:customers',
        ];
    }
}
