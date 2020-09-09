<?php

namespace GetCandy\Api\Core\Customers\Actions;

use GetCandy\Api\Core\Customers\Models\CustomerGroup;
use GetCandy\Api\Core\Customers\Resources\CustomerGroupResource;
use GetCandy\Api\Core\Scaffold\AbstractAction;

class FetchDefaultCustomerGroup extends AbstractAction
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
        return [];
    }

    /**
     * Execute the action and return a result.
     *
     * @return \GetCandy\Api\Core\Channels\Models\Channel
     */
    public function handle()
    {
        return CustomerGroup::with($this->resolveEagerRelations())->whereDefault(true)->first();
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
        if (! $result) {
            return $this->errorNotFound();
        }

        return new CustomerGroupResource($result);
    }
}
