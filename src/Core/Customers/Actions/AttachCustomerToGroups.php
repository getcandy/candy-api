<?php

namespace GetCandy\Api\Core\Customers\Actions;

use GetCandy\Api\Core\Customers\Models\Customer;
use GetCandy\Api\Core\Customers\Models\CustomerGroup;
use GetCandy\Api\Core\Customers\Resources\CustomerResource;
use GetCandy\Api\Core\Foundation\Actions\DecodeIds;
use GetCandy\Api\Core\Scaffold\AbstractAction;

class AttachCustomerToGroups extends AbstractAction
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
        $this->customer = (new FetchCustomer)
            ->actingAs($this->user())
            ->run([
                'encoded_id' => $this->customer_id,
            ]);

        return $this->runningAs('object') || $this->user()->can('update', $this->customer);
    }

    /**
     * Get the validation rules that apply to the action.
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            'customer_group_ids' => 'required|array',
            'customer_id' => 'required|string|hashid_is_valid:'.Customer::class.'|required_without_all:id,handle',
        ];
    }

    /**
     * Execute the action and return a result.
     *
     * @return \GetCandy\Api\Core\Customers\Models\Customer
     */
    public function handle(): Customer
    {
        $realIds = DecodeIds::run([
            'model' => CustomerGroup::class,
            'encoded_ids' => $this->customer_group_ids,
        ]);

        $this->customer->customerGroups()->sync($realIds);

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
