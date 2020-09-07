<?php

namespace GetCandy\Api\Core\Customers\Actions;

use GetCandy\Api\Core\Customers\Models\CustomerGroup;
use GetCandy\Api\Core\Customers\Resources\CustomerGroupResource;
use GetCandy\Api\Core\Scaffold\AbstractAction;
use Illuminate\Support\Arr;

class CreateCustomerGroup extends AbstractAction
{
    /**
     * Determine if the user is authorized to make this action.
     *
     * @return bool
     */
    public function authorize()
    {
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
            'name' => 'required|string',
            'handle' => 'required|unique:customer_groups,handle',
            'default' => 'boolean',
        ];
    }

    /**
     * Execute the action and return a result.
     *
     * @return \GetCandy\Api\Core\Customers\Models\CustomerGroup
     */
    public function handle(): CustomerGroup
    {
        return CustomerGroup::create(
            Arr::except($this->validated(), ['system'])
        )->load($this->resolveEagerRelations());
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
