<?php

namespace GetCandy\Api\Core\Customers\Actions;

use GetCandy\Api\Core\Scaffold\AbstractAction;
use GetCandy\Api\Core\Traits\ReturnsJsonResponses;

class DeleteCustomer extends AbstractAction
{
    use ReturnsJsonResponses;

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
        $this->customer = FetchCustomer::run([
            'encoded_id' => $this->encoded_id,
        ]);

        return $this->user()->can('delete', $this->customer);
    }

    public function afterValidator($validator)
    {
        if ($this->customer->users->count()) {
            $validator->errors()->add('users', 'You must remove or reassign users from this customer first');
        }
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
        return $this->customer->delete();
    }

    /**
     * Returns the response from the action.
     *
     * @param   \GetCandy\Api\Core\Customers\Models\Customer  $result
     * @param   \Illuminate\Http\Request  $request
     *
     * @return  \Illuminate\Http\JsonResponse
     */
    public function response($result, $request): \Illuminate\Http\JsonResponse
    {
        return $this->respondWithNoContent();
    }
}
