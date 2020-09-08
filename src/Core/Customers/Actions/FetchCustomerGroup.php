<?php

namespace GetCandy\Api\Core\Customers\Actions;

use GetCandy\Api\Core\Customers\Models\CustomerGroup;
use GetCandy\Api\Core\Customers\Resources\CustomerGroupResource;
use GetCandy\Api\Core\Scaffold\AbstractAction;
use GetCandy\Api\Core\Traits\ReturnsJsonResponses;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class FetchCustomerGroup extends AbstractAction
{
    use ReturnsJsonResponses;

    /**
     * The fetched address model.
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
        if ($this->encoded_id && ! $this->handle) {
            $this->id = (new CustomerGroup)->decodeId($this->encoded_id);
        }

        try {
            $this->customerGroup = CustomerGroup::with($this->resolveEagerRelations())
                ->withCount($this->resolveRelationCounts())
                ->findOrFail($this->id);
        } catch (ModelNotFoundException $e) {
            if (! $this->runningAs('controller')) {
                throw $e;
            }
        }

        return $this->user() && $this->user()->can('manage-customer-groups');
    }

    /**
     * Get the validation rules that apply to the action.
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            'id' => 'integer|required_without_all:encoded_id,handle',
            'encoded_id' => 'string|hashid_is_valid:'.CustomerGroup::class.'|required_without_all:id,handle',
            'handle' => 'string|required_without_all:encoded_id,id',
        ];
    }

    /**
     * Execute the action and return a result.
     *
     * @return \GetCandy\Api\Core\Customers\Models\Customer|null
     */
    public function handle()
    {
        return $this->customerGroup;
    }

    /**
     * Returns the response from the action.
     *
     * @param   \GetCandy\Api\Core\Customers\Models\CustomerGroup  $result
     * @param   \Illuminate\Http\Request  $request
     *
     * @return  \GetCandy\Api\Core\Customers\Resources\CustomerGroupResource|\Illuminate\Http\JsonResponse
     */
    public function response($result, $request)
    {
        if (! $result) {
            return $this->errorNotFound();
        }

        return new CustomerGroupResource($result);
    }
}
