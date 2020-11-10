<?php

namespace GetCandy\Api\Core\Customers\Actions;

use GetCandy\Api\Core\Customers\Models\CustomerInvite;
use GetCandy\Api\Core\Customers\Resources\CustomerInviteResource;
use GetCandy\Api\Core\Scaffold\AbstractAction;

class CreateCustomerInvite extends AbstractAction
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
            'encoded_id' => 'required|string|hashid_is_valid:'.CustomerInvite::class,
            'email' => 'required|string|unique:customer_invites',
        ];
    }

    /**
     * Execute the action and return a result.
     *
     * @return \Illuminate\Database\Eloquent\Model
     */
    public function handle()
    {
        return $this->customer->invites()->create([
            'email' => $this->email,
        ]);
    }

    /**
     * Returns the response from the action.
     *
     * @param   \Illuminate\Database\Eloquent\Model  $result
     * @param   \Illuminate\Http\Request  $request
     *
     * @return  \GetCandy\Api\Core\Customers\Resources\CustomerInviteResource
     */
    public function response($result, $request)
    {
        return new CustomerInviteResource($result);
    }
}
