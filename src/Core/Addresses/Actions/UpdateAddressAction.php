<?php

namespace GetCandy\Api\Core\Addresses\Actions;

use DateTime;
use GetCandy;
use GetCandy\Api\Core\Addresses\Models\Address;
use GetCandy\Api\Core\Addresses\Resources\AddressResource;
use GetCandy\Api\Core\Countries\Actions\FetchCountry;
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
            'salutation' => 'string',
            'firstname' => 'string',
            'lastname' => 'string',
            'company_name' => 'string',
            'email' => 'email',
            'phone' => 'numeric',
            'address' => 'string',
            'address_two' => 'string',
            'address_three' => 'string',
            'city' => 'string',
            'state' => 'string',
            'postal_code' => 'string',
            'country_id' => 'hashid_is_valid:countries',
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
