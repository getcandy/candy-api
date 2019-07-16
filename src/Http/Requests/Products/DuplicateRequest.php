<?php

namespace GetCandy\Api\Http\Requests\Products;

use GetCandy\Api\Http\Requests\FormRequest;

class DuplicateRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        // return $this->user()->can('create', Product::class);
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
            'routes' => 'required|array',
            'skus' => 'required|array',
            'routes.*.new' => 'required|unique:routes,slug',
            'skus.*.new' => 'required|unique:product_variants,sku',
        ];
    }

    public function messages()
    {
        return [
            'skus.*.new.unique' => 'This SKU has already been taken',
            'skus.*.new.required' => 'This field is required',
            'routes.*.new.unique' => 'This URL has already been taken',
            'routes.*.new.required' => 'This field is required',
        ];
    }
}
