<?php

namespace GetCandy\Api\Core\Payments\Actions;

use GetCandy\Api\Core\Orders\Interfaces\OrderCriteriaInterface;
use GetCandy\Api\Core\Payments\PaymentContract;
use GetCandy\Api\Core\Payments\Resources\PaymentProviderResource;
use GetCandy\Api\Core\Traits\ReturnsJsonResponses;
use Lorisleiva\Actions\Action;

class FetchPaymentProvider extends Action
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
     * @return \GetCandy\Api\Core\Addresses\Models\Address
     */
    public function handle($handle, OrderCriteriaInterface $orders, PaymentContract $payments)
    {
        $order = $this->order_id ? $orders->id($this->order_id)->first() : null;

        try {
            $provider = $payments->with($handle)->order($order);
        } catch (\InvalidArgumentException $e) {
            return null;
        }

        return $provider;
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
        if (! $result) {
            return $this->errorNotFound();
        }

        return new PaymentProviderResource($result);
    }
}
