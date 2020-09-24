<?php

namespace GetCandy\Api\Core\Customers\Actions;

use GetCandy\Api\Core\Customers\Models\Customer;
use GetCandy\Api\Core\Customers\Resources\CustomerResource;
use GetCandy\Api\Core\Scaffold\AbstractAction;

class UpdateCustomer extends AbstractAction
{
    /**
     * The address object we want to update.
     *
     * @var \GetCandy\Api\Core\Customers\Models\Customer
     */
    protected $customer;

    /**
     * Determine if the user is authorized to make this action.
     *
     * @return bool
     */
    public function authorize()
    {
        $this->customer = FetchCustomer::run([
            'encoded_id' => $this->encoded_id,
        ]);

        return $this->user()->can('update', $this->customer);
    }

    /**
     * Get the validation rules that apply to the action.
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            'title' => 'nullable|string',
            'firstname' => 'nullable|string',
            'lastname' => 'nullable|string',
            'contact_number' => 'nullable|string',
            'alt_contact_number' => 'nullable|string',
            'vat_no' => 'nullable|alpha_num',
            'company_name' => 'nullable|string',
            'fields' => 'nullable|array',
        ];
    }

    /**
     * Execute the action and return a result.
     *
     * @return \GetCandy\Api\Core\Customers\Models\Customer
     */
    public function handle(): Customer
    {
        $this->customer->update($this->validated());

        return $this->customer;
    }

    /**
     * Returns the response from the action.
     *
     * @param   \GetCandy\Api\Core\Customers\Models\Customer  $result
     * @param   \Illuminate\Http\Request  $request
     *
     * @return  \GetCandy\Api\Core\Customers\Resources\CustomerResource
     */
    public function response($result, $request): CustomerResource
    {
        return new CustomerResource($result);
    }
}
