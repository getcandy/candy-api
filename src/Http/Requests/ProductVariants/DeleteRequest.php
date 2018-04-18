<?php

namespace GetCandy\Api\Http\Requests\ProductVariants;

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
        ];
    }
}
