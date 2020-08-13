<?php

namespace GetCandy\Api\Core\Addresses\Actions;

use Lorisleiva\Actions\Action;
use GetCandy\Api\Core\Addresses\Models\Address;
use GetCandy\Api\Core\Countries\Models\Country;

class NewAddressAction extends Action
{
    /**
     * Determine if the user is authorized to make this action.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the action.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'salutation' => 'string',
            'firstname' => 'required|string',
            'lastname' => 'required|string',
            'company_name' => 'string',
            'email' => 'email',
            'phone' => 'numeric',
            'address' => 'required|string',
            'address_two' => 'string',
            'address_three' => 'string',
            'city' => 'required|string',
            'state' => 'required|string',
            'postal_code' => 'required|string',
            'country_id' => 'required|hashid_is_valid:countries',
            'shipping' => 'boolean',
            'billing' => 'boolean',
            'default' => 'boolean',
            'last_used_at' => 'datetime',
            'delivery_instructions' => 'string',
            'meta' => 'array'
        ];
    }

    /**
     * Execute the action and return a result.
     *
     * @return mixed
     */
    public function handle()
    {
        $address = new Address;
        $attributes = $this->validated();

        if (!empty($attributes['country_id'])) {
            $realId = (new Country)->decodeId($attributes['country_id']);
            $country = Country::find($realId);
            $attributes['country_id'] = $country->id;
        }

        $address->fill($attributes);
        $address->user()->associate($this->user());
        $address->save();

        return $address;
    }
}
