<?php
namespace GetCandy\Api\Http\Requests\Baskets;

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
        // return $this->user()->can('create', Category::class);
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'variants' => 'array|unique_lines',
            'variants.*.id' => 'required|hashid_is_valid:product_variants',
            'variants.*.quantity' => 'required|numeric|min:1',
            'basket_id' => 'hashid_is_valid:baskets'
        ];
    }
}
