<?php

namespace GetCandy\Api\Http\Requests\Shipping\Pricing;

use GetCandy\Api\Http\Requests\FormRequest;
use GetCandy\Api\Core\Shipping\Models\ShippingZone;

class StoreRequest extends FormRequest
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
    public function rules()
    {
        return [
            'rate' => 'required|numeric',
            'zone_id' => 'required|hashid_is_valid:'.ShippingZone::class,
            'currency_id' => 'required|hashid_is_valid:currencies',
        ];
    }
}
