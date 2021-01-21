<?php

namespace GetCandy\Api\Core\Addresses\Actions;

use DateTime;
use GetCandy;
use GetCandy\Api\Core\Addresses\Models\Address;
use GetCandy\Api\Core\Addresses\Resources\AddressResource;
use GetCandy\Api\Core\Countries\Actions\FetchCountry;
use GetCandy\Api\Core\Countries\Models\Country;
use GetCandy\Api\Core\Customers\Actions\FetchCustomer;
use GetCandy\Api\Core\Customers\Models\Customer;
use GetCandy\Api\Core\Users\Actions\FetchUserAction;
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
        if (! $this->user_id) {
            return $this->user()->can('create', Address::class);
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
        $userModel = GetCandy::getUserModel();

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
            'country_id' => 'required|hashid_is_valid:'.Country::class,
            'user_id' => 'string|hashid_is_valid:'.$userModel,
            'customer_id' => 'string|hashid_is_valid:'.Customer::class,
            'shipping' => 'boolean',
            'billing' => 'boolean',
            'default' => 'boolean',
            'last_used_at' => 'date_format:'.DateTime::ATOM,
            'delivery_instructions' => 'string',
            'meta' => 'array',
        ];
    }

    /**
     * Execute the action and return a result.
     *
     * @return \GetCandy\Api\Core\Addresses\Models\Address
     */
    public function handle()
    {
        $user = $this->user();
        $model = GetCandy::getUserModel();
        if ($this->user_id) {
            $user = FetchUserAction::run([
                'encoded_id' => $this->user_id,
            ]);
        }

        if ($this->customer_id) {
            $customer = FetchCustomer::run([
                'encoded_id' => $this->customer_id,
            ]);
            $model = get_class($customer);
        }

        $address = new Address;
        $attributes = array_merge(
            Arr::except($this->validated(), ['user_id', 'customer_id', 'country_id']),
            [
                'addressable_id' => $this->customer_id ? $customer->id : $user->id,
                'addressable_type' => $model,
            ]
        );

        $country = FetchCountry::run([
            'encoded_id' => $this->country_id,
        ]);

        $address->fill($attributes);
        $address->country()->associate($country);
        $address->save();

        return $address;
    }

    /**
     * Returns the response from the action.
     *
     * @param   \GetCandy\Api\Core\Addresses\Models\Address  $result
     * @param   \Illuminate\Http\Request  $request
     *
     * @return  \GetCandy\Api\Core\Addresses\Resources\AddressResource
     */
    public function response($result, $request)
    {
        return new AddressResource($result);
    }
}
