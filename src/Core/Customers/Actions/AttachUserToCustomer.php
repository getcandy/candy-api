<?php

namespace GetCandy\Api\Core\Customers\Actions;

use GetCandy;
use GetCandy\Api\Core\Customers\Models\Customer;
use GetCandy\Api\Core\Customers\Resources\CustomerResource;
use GetCandy\Api\Core\Scaffold\AbstractAction;

class AttachUserToCustomer extends AbstractAction
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
                'encoded_id' => $this->encoded_id,
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
            'user_id' => 'required|string|hashid_is_valid:'.GetCandy::getUserModel().'|required_without_all:id,handle',
        ];
    }

    /**
     * Execute the action and return a result.
     *
     * @return \GetCandy\Api\Core\Customers\Models\Customer
     */
    public function handle(): Customer
    {
        $userModel = GetCandy::getUserModel();
        $realUserId = (new $userModel)->decodeId($this->user_id);
        $user = (new $userModel)->find($realUserId);
        $this->customer->users()->save($user);

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
