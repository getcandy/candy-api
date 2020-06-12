<?php

namespace GetCandy\Api\Http\Controllers\Orders;

use GetCandy\Api\Core\Orders\Services\OrderLineService;
use GetCandy\Api\Http\Controllers\BaseController;
use GetCandy\Api\Http\Requests\Orders\Lines\CreateRequest;
use GetCandy\Api\Http\Requests\Orders\Lines\DeleteRequest;
use GetCandy\Api\Http\Resources\Orders\OrderResource;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class OrderLineController extends BaseController
{
    /**
     * @var \GetCandy\Api\Core\Orders\Services\OrderLineService
     */
    protected $orderLines;

    public function __construct(OrderLineService $lines)
    {
        $this->orderLines = $lines;
    }

    /**
     * Handles the request to store a new order line.
     *
     * @param  string  $orderId
     * @param  \GetCandy\Api\Http\Requests\Orders\Lines\CreateRequest  $request
     * @return \GetCandy\Api\Http\Resources\Orders\OrderResource
     */
    public function store($orderId, CreateRequest $request)
    {
        try {
            $result = $this->orderLines->store($orderId, $request->all(), $request->is_manual);
        } catch (ModelNotFoundException $e) {
            return $this->errorNotFound();
        }

        return new OrderResource($result);
    }

    /**
     * Handles the request to remove an order line.
     *
     * @param  string  $lineId
     * @param  \GetCandy\Api\Http\Requests\Orders\Lines\DeleteRequest  $request
     * @return array
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
