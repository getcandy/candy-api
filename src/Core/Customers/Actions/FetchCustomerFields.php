<?php

namespace GetCandy\Api\Core\Customers\Actions;

use GetCandy\Api\Core\Traits\ReturnsJsonResponses;
use Lorisleiva\Actions\Action;

class FetchCustomerFields extends Action
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
        return config('getcandy.customers.fields', []);
    }

    /**
     * Returns the response from the action.
     *
     * @param   array  $result
     * @param   \Illuminate\Http\Request  $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function response($result, $request)
    {
        return $this->respondWithArray([
            'data' => [
                'fields' => $result,
            ],
        ]);
    }
}
