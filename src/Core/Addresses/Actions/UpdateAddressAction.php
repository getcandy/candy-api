<?php

namespace GetCandy\Api\Core\Addresses\Actions;

use DateTime;
use GetCandy;
use GetCandy\Api\Core\Addresses\Models\Address;
use GetCandy\Api\Core\Addresses\Resources\AddressResource;
use GetCandy\Api\Core\Countries\Actions\FetchCountry;
use GetCandy\Api\Core\Countries\Models\Country;
use Illuminate\Support\Arr;
use Lorisleiva\Actions\Action;

class UpdateAddressAction extends Action
{
    /**
     * The address object we want to update.
     *
     * @var \GetCandy\Api\Core\Addresses\Models\Address
     */
    protected $address;

    /**
     * Determine if the user is authorized to make this action.
     *
     * @return bool
     */
    public function authorize()
    {
        $this->address = FetchAddressAction::run([
            'encoded_id' => $this->addressId,
        ]);

        return $this->user()->can('update', $this->address);
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
            'salutation' => 'string|nullable',
            'firstname' => 'string|nullable',
            'lastname' => 'string|nullable',
            'company_name' => 'string|nullable',
            'email' => 'email|nullable',
            'phone' => 'nullable',
            'address' => 'string|nullable',
            'address_two' => 'string|nullable',
            'address_three' => 'string|nullable',
            'city' => 'string|nullable',
            'state' => 'string|nullable',
            'postal_code' => 'string|nullable',
            'country_id' => 'hashid_is_valid:'.Country::class,
            'shipping' => 'boolean|nullable',
            'billing' => 'boolean|nullable',
            'default' => 'boolean|nullable',
            'last_used_at' => 'nullable|date_format:'.DateTime::ATOM,
            'delivery_instructions' => 'string|nullable',
            'meta' => 'array|nullable',
        ];
    }

    /**
     * Execute the action and return a result.
     *
     * @return \GetCandy\Api\Core\Addresses\Models\Address
     */
    public function handle()
    {
        $attributes = Arr::except($this->validated(), ['user_id', 'customer_id', 'country_id']);

        if ($this->country_id) {
            $country = FetchCountry::run([
                'encoded_id' => $this->country_id,
            ]);
            $this->address->country()->associate($country);
        }

        $this->address->fill($attributes);
        $this->address->save();

        return $this->address;
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
