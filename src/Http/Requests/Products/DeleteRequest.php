<?php

namespace GetCandy\Api\Http\Requests\Products;

use GetCandy\Api\Http\Requests\FormRequest;

class DeleteRequest extends FormRequest
{
    public function authorize()
    {
        return $this->user()->hasRole('admin');
        // return $this->user()->can('delete', Product::class);
    }

    public function rules()
    {
        return [
            'product' => 'hashid_is_valid:products',
        ];
    }
}
