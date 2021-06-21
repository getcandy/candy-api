<?php

namespace GetCandy\Api\Core\Orders\Actions;

use GetCandy\Api\Core\Orders\Models\Order;
use GetCandy\Api\Core\Scaffold\AbstractAction;
use GetCandy\Api\Core\Traits\ReturnsJsonResponses;
use GetCandy\Api\Http\Resources\Orders\OrderResource;

class ExtendOrderExpiry extends AbstractAction
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
    public function rules(): array
    {
        return [
            'id' => 'string|hashid_is_valid:'.Order::class,
        ];
    }

    /**
     * Execute the action and return a result.
     *
     * @return \GetCandy\Api\Core\Orders\Models\Order|null
     */
    public function handle()
    {
        $orderId = (new Order)->decodeId($this->id);

        $order = Order::find($orderId);

        $config = config('getcandy.orders.pending_orders', [
            'timeout' => 30,
            'timeout_auto_extend' => false,
        ]);

        $order->update([
            'expires_at' => now()->addMinutes($config['timeout']),
        ]);

        return $order->refresh();
    }

    /**
     * Returns the response from the action.
     *
     * @param   \GetCandy\Api\Core\Orders\Models\Order|null  $result
     * @param   \Illuminate\Http\Request  $request
     *
     * @return  \GetCandy\Api\Core\Orders\Resources\OrderResource|\Illuminate\Http\JsonResponse
     */
    public function response($result, $request)
    {
        if (! $result) {
            return $this->errorNotFound();
        }

        return new OrderResource($result);
    }
}
