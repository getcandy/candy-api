<?php

namespace GetCandy\Api\Core\Customers\Actions;

use GetCandy\Api\Core\Customers\Models\CustomerGroup;
use GetCandy\Api\Core\Customers\Resources\CustomerGroupResource;
use GetCandy\Api\Core\Scaffold\AbstractAction;

class UpdateCustomerGroup extends AbstractAction
{
    /**
     * The address object we want to update.
     *
     * @var \GetCandy\Api\Core\Customers\Models\CustomerGroup
     */
    protected $customerGroup;

    /**
     * Determine if the user is authorized to make this action.
     *
     * @return bool
     */
    public function authorize()
    {
        $this->customerGroup = FetchCustomerGroup::run([
            'encoded_id' => $this->encoded_id,
        ]);

        return $this->user()->can('manage-customer-groups');
    }

    /**
     * Get the validation rules that apply to the action.
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            'name' => "unique:channels,name,{$this->customerGroup->id}",
            'handle' => "unique:channels,handle,{$this->customerGroup->id}",
            'default' => 'boolean|in:true,1',
            'url' => 'string',
        ];
    }

    /**
     * Execute the action and return a result.
     *
     * @return \GetCandy\Api\Core\Customers\Models\CustomerGroup
     */
    public function handle(): CustomerGroup
    {
        $this->customerGroup->update($this->validated());

        return $this->customerGroup;
    }

    /**
     * Returns the response from the action.
     *
     * @param   \GetCandy\Api\Core\Customers\Models\CustomerGroup  $result
     * @param   \Illuminate\Http\Request  $request
     *
     * @return  \GetCandy\Api\Core\Customers\Resources\CustomerGroupResource
     */
    public function response($result, $request): CustomerGroupResource
    {
        return new CustomerGroupResource($result);
    }
}
