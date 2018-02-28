<?php

namespace GetCandy\Api\Http\Requests\Products;

use GetCandy\Api\Http\Requests\FormRequest;
use GetCandy\Api\Products\Models\Product;

class UpdateCollectionsRequest extends FormRequest
{
    public function authorize()
    {
        return $this->user()->hasRole('admin');
    }

    public function rules()
    {
        return [
            'collections' => 'required|array'
        ];
    }
}
