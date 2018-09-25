<?php

namespace GetCandy\Api\Http\Requests\Orders;

use GetCandy\Api\Http\Requests\FormRequest;

class StoreAddressRequest extends FormRequest
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
        // We can choose to ignore the validation.
        // Useful if we don't need to set most fields.
        if ($this->force) {
            return [];
        }

        return [
            'firstname' => 'required',
            'lastname' => 'required',
            'address_id' => 'hashid_is_valid:addresses',
            'address' => 'required_without:address_id',
            'city' => 'required_without:address_id',
            'county' => 'required_without_all:address_id,state',
            'email' => 'email',
            'state' => 'required_without_all:address_id,county',
            'zip' => 'required_without:address_id',
            'country' => 'required_without:address_id',
        ];
    }
}
