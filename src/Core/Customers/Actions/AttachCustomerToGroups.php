<?php

namespace GetCandy\Api\Core\Customers\Actions;

use GetCandy;
use GetCandy\Api\Core\Scaffold\AbstractAction;
use GetCandy\Api\Core\Customers\Models\Customer;
use GetCandy\Api\Core\Customers\Models\CustomerGroup;
use GetCandy\Api\Core\Customers\Resources\CustomerResource;

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
                'encoded_id' => $this->encoded_id,
            ]);
                dd(1);
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
            'customer_group_ids' => 'required|array',
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

        dd($realIds);
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
