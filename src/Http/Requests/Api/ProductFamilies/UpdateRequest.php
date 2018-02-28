<?php

namespace GetCandy\Api\Http\Requests\ProductFamilies;

use GetCandy\Api\Http\Requests\FormRequest;
use GetCandy\Api\Products\Models\ProductFamily;

class UpdateRequest extends FormRequest
{
    public function authorize()
    {
        // return $this->user()->can('create', ProductFamily::class);
        return $this->user()->hasRole('admin');
    }
    public function rules(ProductFamily $family)
    {
        return [
            'attributes' => 'array|required',
        ];
    }
}
