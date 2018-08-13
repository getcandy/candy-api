<?php

namespace GetCandy\Api\Http\Requests\ProductVariants;

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
        $rules = [
            'variants' => 'array|min:1',
            'options.*.options' => 'array|min:1',
            'options' => 'array|min:1',
            'variants.*.price' => 'numeric|required',
        ];

        foreach (collect($this->variants) as $index => $variant) {
            if (empty($variant['id'])) {
                $rules['variants.*.sku'] = 'unique:product_variants';
                $rules['variants.*.price'] = 'required';
            }
        }

        // dd($rules);

        return $rules;
    }

    public function messages()
    {
        return [
            'variants.*.sku.unique' => 'This SKU has already been taken',
            'variants.*.sku.required' => 'The SKU field is required',
            'options.*.options.min' => 'You must specify options',
            'variants.*.price.required' => 'The price field is required',
            'variants.*.price.numeric' => 'The price field must be a number',
            'variants.min' => 'You must generate at least one variant',
            'options.min' => 'You must generate at least one option',
        ];
    }
}
