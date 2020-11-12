<?php

namespace GetCandy\Api\Core\Customers\Actions;

use GetCandy\Api\Core\Customers\Models\Customer;
use GetCandy\Api\Core\Customers\Models\CustomerInvite;
use GetCandy\Api\Core\Scaffold\AbstractAction;

class FetchCustomerInvite extends AbstractAction
{
    /**
     * The fetched customer invite model.
     *
     * @var CustomerInvite
     */
    protected $customerInvite;

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
            'encoded_id' => 'string|hashid_is_valid:'.CustomerInvite::class.'|required_without_all:customer_id,email',
            'customer_id' => 'string|hashid_is_valid:'.Customer::class.'|required_without:encoded_id',
            'email' => 'string|email|required_without:encoded_id',
        ];
    }

    /**
     * Execute the action and return a result.
     *
     * @return \GetCandy\Api\Core\Customers\Models\CustomerInvite|null
     */
    public function handle()
    {
        if ($this->encoded_id) {
            return CustomerInvite::find((new CustomerInvite)->decodeId($this->encoded_id));
        }

        return CustomerInvite::where('customer_id', (new Customer)->decodeId($this->customer_id))
            ->where('email', $this->email)
            ->first();
    }
}
