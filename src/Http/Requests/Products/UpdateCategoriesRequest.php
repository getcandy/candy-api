<?php

namespace GetCandy\Api\Http\Requests\Products;

use GetCandy\Api\Http\Requests\FormRequest;

class UpdateCategoriesRequest extends FormRequest
{
    public function authorize()
    {
        return $this->user()->hasRole('admin');
    }

    public function rules()
    {
        return [
            'categories' => 'required|array|min:1',
            'categories.*' => 'required|hashid_is_valid:GetCandy\Api\Core\Categories\Models\Category',
        ];
    }

    public function attributes()
    {
        return [
            'categories.*' => 'category',
        ];
    }
}
