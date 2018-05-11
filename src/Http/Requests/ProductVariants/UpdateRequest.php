<?php

namespace GetCandy\Api\Http\Requests\ProductVariants;

use Illuminate\Foundation\Http\FormRequest;
use GetCandy\Api\Core\Products\Models\ProductVariant;

class UpdateRequest extends FormRequest
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
    public function rules(ProductVariant $variant)
    {
        return [
            'sku' => 'required|unique:product_variants,sku,'.$variant->decodeId($this->variant),
            'pricing' => 'array',
            'pricing.*.customer_group_id' => 'required|hashid_is_valid:customer_groups',
        ];
    }
}
