<?php

namespace GetCandy\Api\Http\Requests\Products;

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
        return $this->user()->hasRole('admin');
        // return $this->user()->can('delete', Product::class);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'product' => 'hashid_is_valid:products',
        ];
    }
}
