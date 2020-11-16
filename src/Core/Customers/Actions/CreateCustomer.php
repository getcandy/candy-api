<?php

namespace GetCandy\Api\Core\Customers\Actions;

use GetCandy;
use GetCandy\Api\Core\Customers\Models\Customer;
use GetCandy\Api\Core\Customers\Resources\CustomerResource;
use GetCandy\Api\Core\Scaffold\AbstractAction;
use Illuminate\Support\Arr;

class CreateCustomer extends AbstractAction
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
    public function rules(): array
    {
        return [
            'user_id' => 'nullable|string|hashid_is_valid:'.GetCandy::getUserModel(),
            'title' => 'nullable|string',
            'firstname' => 'nullable|string',
            'lastname' => 'nullable|string',
            'contact_number' => 'nullable|string',
            'alt_contact_number' => 'nullable|string',
            'vat_no' => 'nullable|string',
            'company_name' => 'nullable|string',
            'fields' => 'nullable',
            'customer_group_ids' => 'nullable|array',
        ];
    }

    /**
     * Execute the action and return a result.
     *
     * @return \GetCandy\Api\Core\Customers\Models\Customer
     */
    public function handle(): Customer
    {
        $customer = Customer::create(
            Arr::except($this->validated(), ['user_id', 'country_id', 'customer_group_ids'])
        );
        if ($this->user_id) {
            AttachUserToCustomer::run([
                'encoded_id' => $customer->encoded_id,
                'user_id' => $this->user_id,
            ]);
        }

        AttachCustomerToGroups::run([
            'customer_id' => $customer->encoded_id,
            'customer_group_ids' => $this->customer_group_ids ?: [FetchDefaultCustomerGroup::run()->encoded_id],
        ]);

        return $customer->load($this->resolveEagerRelations());
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
