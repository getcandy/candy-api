<?php

namespace GetCandy\Api\Core\Addresses\Actions;

use DateTime;
use GetCandy;
use GetCandy\Api\Core\Addresses\Models\Address;
use GetCandy\Api\Core\Countries\Actions\FetchCountryAction;
use GetCandy\Api\Core\Users\Actions\FetchUserAction;
use GetCandy\Api\Http\Resources\Addresses\AddressResource;
use Illuminate\Support\Arr;
use Lorisleiva\Actions\Action;

class CreateAddressAction extends Action
{
    /**
     * Determine if the user is authorized to make this action.
     *
     * @return bool
     */
    public function authorize()
    {
        if (!$this->user_id) {
            return $this->user()->can('create-address');
        }
        return $this->user()->can('manage-addresses');
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
            'user_id' => 'string|hashid_is_valid:users',
            'shipping' => 'boolean',
            'billing' => 'boolean',
            'default' => 'boolean',
            'last_used_at' => 'date_format:' . DateTime::ATOM,
            'delivery_instructions' => 'string',
            'meta' => 'array'
        ];
    }

    /**
     * Execute the action and return a result.
     *
     * @return \GetCandy\Api\Core\Addresses\Models\Address
     */
    public function handle()
    {
        if ($this->user_id) {
            $user = FetchUserAction::run([
                'encoded_id' => $this->user_id,
            ]);
        } else {
            $user = $this->user();
        }

        $address = new Address;
        $attributes = Arr::except($this->validated(), ['user_id', 'country_id']);

        $country = FetchCountryAction::run([
            'encoded_id' => $this->country_id
        ]);

        $address->fill($attributes);
        $address->country()->associate($country);
        $address->user()->associate($user);
        $address->save();

        return $address;
    }

    /**
     * Returns the response from the action
     *
     * @param   \GetCandy\Api\Core\Addresses\Models\Address  $result
     * @param   \Illuminate\Http\Request  $request
     *
     * @return  \GetCandy\Api\Http\Resources\Addresses\AddressResource
     */
    public function response($result, $request)
    {
        return new AddressResource($result);
    }
}
