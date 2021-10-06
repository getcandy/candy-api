<?php

namespace GetCandy\Api\Core\Payments\Actions;

use GetCandy\Api\Core\Orders\Models\Order;
use GetCandy\Api\Core\Payments\Models\Transaction;
use GetCandy\Api\Core\Traits\ReturnsJsonResponses;
use GetCandy\Api\Http\Resources\Transactions\TransactionResource;
use Lorisleiva\Actions\Action;

class CreateTransaction extends Action
{
    use ReturnsJsonResponses;

    /**
     * Determine if the user is authorized to make this action.
     *
     * @return bool
     */
    public function authorize()
    {
        return $this->user()->can('create-transactions');
    }

    /**
     * Get the validation rules that apply to the action.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'order_id' => 'required|hashid_is_valid:'.Order::class,
            'amount' => 'required|numeric|min:1',
            'merchant' => 'required',
            'success' => 'required',
            'refund' => 'nullable',
            'status' => 'required',
            'manual' => 'nullable',
            'card_type' => 'nullable',
            'last_four' => 'nullable|numeric|max:9999',
            'provider' => 'nullable',
            'driver' => 'nullable',
        ];
    }

    public function afterValidator($validator)
    {
        $orderId = (new Order())->decodeId($this->order_id);
        $order = Order::withoutGlobalScopes()->with('transactions')->find($orderId);

        if (! $this->order_id || ! $order) {
            return;
        }

        $transactionTotal = $order->transactions()->whereSuccess(true)->whereRefund(false)->sum('amount');
        $remaining = $order->order_total - $transactionTotal;

        if (! $this->refund && $this->amount > $remaining) {
            $validator->errors()->add('field', 'Amount cannot be more than '.$remaining);
        }
    }

    /**
     * Execute the action and return a result.
     *
     * @return \GetCandy\Api\Core\Addresses\Models\Address
     */
    public function handle()
    {
        $orderId = (new Order())->decodeId($this->order_id);

        $transaction = new Transaction();
        $transaction->fill(
            $this->except(['order_id'])
        );
        if ($this->refund) {
            $transaction->amount = -1 * abs($this->amount);
        }
        $transaction->order_id = $orderId;
        $transaction->save();

        return $transaction;
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
        return new TransactionResource($result);
    }
}
