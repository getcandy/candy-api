<?php

namespace GetCandy\Api\Core\Users\Actions;

use GetCandy\Api\Core\Customers\Actions\FetchCustomerFields;
use GetCandy\Api\Core\Traits\ReturnsJsonResponses;
use Lorisleiva\Actions\Action;

class FetchUserFields extends Action
{
    use ReturnsJsonResponses;

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
    public function rules()
    {
        return [];
    }

    /**
     * Execute the action and return a result.
     *
     * @return mixed
     */
    public function handle()
    {
        return $this->delegateTo(FetchCustomerFields::class);
    }
}
