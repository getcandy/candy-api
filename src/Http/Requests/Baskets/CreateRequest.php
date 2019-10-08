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
        $rules = [
            'variants' => 'array|unique_lines',
            'basket_id' => 'hashid_is_valid:baskets',
        ];

        $variants = app('api')->productVariants()->getByHashedIds(
            collect($this->variants)->pluck('id')->toArray()
        );

        foreach ($this->variants ?? [] as $i => $v) {
            $variant = $variants->first(function ($variant) use ($v) {
                return $variant->encodedId() === $v['id'] ?? null;
            });
            if ($variant) {
                $rules["variants.{$i}.quantity"] = 'required|numeric|min:1|min_quantity:'.$variant->min_qty.'|min_batch:'.$variant->min_batch.'|in_stock:'.$v['id'] ?? '0';
            }
            $rules["variants.{$i}.id"] = 'required|hashid_is_valid:product_variants';
        }

        return $rules;
    }

    public function messages()
    {
        return [
            'variants.*.id.hashid_is_valid' => trans('getcandy::validation.hashid_is_valid'),
            'variants.*.quantity.min_quantity' => trans('getcandy::validation.min_qty'),
            'variants.*.quantity.min_batch' => trans('getcandy::validation.min_batch'),
        ];
    }
}
