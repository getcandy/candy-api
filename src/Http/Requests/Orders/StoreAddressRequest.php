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
            'firstname' => 'required_without:address_id',
            'lastname' => 'required_without:address_id',
            'address_id' => 'hashid_is_valid:addresses',
            'address' => 'required_without:address_id|max:40',
            'city' => 'required_without:address_id|max:40',
            'county' => 'required_without_all:address_id,state|max:40',
            'email' => 'email',
            'state' => 'required_without_all:address_id,county|max:40',
            'zip' => 'max:8',
            'country' => 'required_without:address_id',
        ];
    }
}
