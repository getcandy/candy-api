<?php

namespace GetCandy\Api\Core\ReusablePayments\Actions;

use GetCandy\Api\Core\ReusablePayments\Models\ReusablePayment;
use GetCandy\Api\Core\ReusablePayments\Resources\ReusablePaymentResource;
use Lorisleiva\Actions\Action;

class CreateReusablePayment extends Action
{
    /**
     * Determine if the user is authorized to make this action.
     *
     * @return bool
     */
    public function authorize()
    {
        return $this->user()->can('create');
    }

    /**
     * Get the validation rules that apply to the action.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'user_id' => 'required|exists:users,id',
            'type' => 'required|string',
            'provider' => 'required|string',
            'last_four' => 'required|digits:4',
            'token' => 'required|string',
            'expires_at' => 'required|date',
        ];
    }

    /**
     * Execute the action and return a result.
     *
     * @return mixed
     */
    public function handle()
    {
        return ReusablePayment::create($this->validated());
    }

    /**
     * Returns the response from the action.
     *
     * @param \GetCandy\Api\Core\ReusablePayments\Models\ReusablePayment  $result
     * @param \Illuminate\Http\Request  $request
     * @return \GetCandy\Api\Core\ReusablePayments\Resources\ReusablePaymentResource
     */
    public function response($result, $request)
    {
        return new ReusablePaymentResource($result);
    }
}
