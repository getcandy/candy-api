<?php

namespace GetCandy\Api\Core\Customers\Actions;

use GetCandy\Api\Core\Exceptions\DefaultRecordRequiredException;
use GetCandy\Api\Core\Scaffold\AbstractAction;
use GetCandy\Api\Core\Traits\ReturnsJsonResponses;
use Illuminate\Http\JsonResponse;

class DeleteCustomerGroup extends AbstractAction
{
    use ReturnsJsonResponses;

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
        $this->customerGroup = $this->delegateTo(FetchCustomerGroup::class);

        return $this->user()->can('delete', $this->customerGroup);
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
     * @return bool
     */
    public function handle()
    {
        if ($this->customerGroup->default) {
            if (! $this->runningAs('controller')) {
                throw new DefaultRecordRequiredException;
            }

            return false;
        }

        $this->customerGroup->customers()->detach();
        $this->customerGroup->products()->detach();
        $this->customerGroup->collections()->detach();
        $this->customerGroup->categories()->detach();
        $this->customerGroup->shippingPrices()->detach();

        return $this->customerGroup->delete();
    }

    /**
     * Returns the response from the action.
     *
     * @param   bool $result
     * @param   \Illuminate\Http\Request  $request
     *
     * @return  \Illuminate\Http\JsonResponse
     */
    public function response($result, $request): JsonResponse
    {
        if (! $result) {
            return $this->errorUnprocessable('You cannot remove the default record.');
        }

        return $this->respondWithNoContent();
    }
}
