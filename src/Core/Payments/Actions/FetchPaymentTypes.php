<?php

namespace GetCandy\Api\Core\Payments\Actions;

use GetCandy\Api\Core\Payments\Models\PaymentType;
use GetCandy\Api\Core\Payments\Resources\PaymentTypeCollection;
use Lorisleiva\Actions\Action;

class FetchPaymentTypes extends Action
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
    public function rules()
    {
        return [];
    }

    /**
     * Execute the action and return a result.
     *
     * @return \GetCandy\Api\Core\Addresses\Models\Address
     */
    public function handle()
    {
        return PaymentType::get();
    }

    /**
     * Returns the response from the action.
     *
     * @param   \GetCandy\Api\Core\Addresses\Models\Address  $result
     * @param   \Illuminate\Http\Request  $request
     *
     * @return  json
     */
    public function response($result, $request)
    {
        return new PaymentTypeCollection($result);
    }
}
