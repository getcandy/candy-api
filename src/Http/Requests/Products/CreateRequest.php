<?php

namespace GetCandy\Api\Http\Requests\Products;

use GetCandy\Api\Http\Requests\FormRequest;

class CreateRequest extends FormRequest
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
            'name' => 'required|valid_structure:products',
            'url' => 'required|unique:routes,slug',
            'stock' => 'required|numeric',
            'family_id' => 'required',
            'price' => 'required',
            'sku' => 'required|unique:product_variants,sku',
        ];
    }
}
