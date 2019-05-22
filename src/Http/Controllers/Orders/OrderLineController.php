<?php

namespace GetCandy\Api\Http\Controllers\Orders;

use GetCandy\Api\Http\Controllers\BaseController;
use GetCandy\Api\Core\Orders\Services\OrderLineService;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use GetCandy\Api\Http\Requests\Orders\Lines\CreateRequest;
use GetCandy\Api\Http\Requests\Orders\Lines\DeleteRequest;
use GetCandy\Api\Http\Transformers\Fractal\Orders\OrderTransformer;

class OrderLineController extends BaseController
{
    protected $orderLines;

    public function __construct(OrderLineService $lines)
    {
        $this->orderLines = $lines;
    }

    /**
     * Handles the request to store a new order line.
     *
     * @param string $orderId
     * @param CreateRequest $request
     * @param OrderLineService $lines
     * @return void
     */
    public function store($orderId, CreateRequest $request)
    {
        try {
            $result = $this->orderLines->store($orderId, $request->all(), $request->is_manual);
        } catch (ModelNotFoundException $e) {
            return $this->errorNotFound();
        }

        return $this->respondWithItem($result, new OrderTransformer);
    }

    /**
     * Handles the request to remove an order line.
     *
     * @param string $lineId
     * @param DeleteRequest $request
     * @return void
     */
    public function destroy($lineId, DeleteRequest $request)
    {
        try {
            $result = $this->orderLines->delete($lineId);
        } catch (ModelNotFoundException $e) {
            //
        }

        return $this->respondWithSuccess();
    }
}
