<?php

namespace GetCandy\Api\Http\Controllers\Shipping;

use Illuminate\Http\Request;
use GetCandy\Api\Http\Controllers\BaseController;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use GetCandy\Api\Http\Requests\Shipping\Pricing\StoreRequest;
use GetCandy\Api\Http\Requests\Shipping\Pricing\EstimateRequest;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use GetCandy\Api\Http\Transformers\Fractal\Shipping\ShippingPriceTransformer;

class ShippingPriceController extends BaseController
{
    /**
     * Returns a listing of channels.
     * @return Json
     */
    public function index(Request $request)
    {
        $orders = app('api')->shippingPrices()->getPaginatedData($request->per_page, $request->current_page);

        return $this->respondWithCollection($orders, new ShippingPriceTransformer);
    }

    /**
     * Handles the request to show a channel based on it's hashed ID.
     * @param  string $id
     * @return Json
     */
    public function show($id)
    {
        try {
            $channel = app('api')->shippingPrices()->getByHashedId($id);
        } catch (ModelNotFoundException $e) {
            return $this->errorNotFound();
        }

        return $this->respondWithItem($channel, new ShippingPriceTransformer);
    }

    /**
     * Handles the request to create a new channel.
     * @param  CreateRequest $request
     * @return Json
     */
    public function store($id, StoreRequest $request)
    {
        $result = app('api')->shippingPrices()->create($id, $request->all());

        return $this->respondWithItem($result, new ShippingPriceTransformer);
    }

    public function update($id, StoreRequest $request)
    {
        try {
            $result = app('api')->shippingPrices()->update($id, $request->all());
        } catch (NotFoundHttpException $e) {
            return $this->errorNotFound();
        }

        return $this->respondWithItem($result, new ShippingPriceTransformer);
    }

    public function estimate(EstimateRequest $request)
    {
        $result = app('api')->shippingPrices()->estimate($request->amount, $request->zip, $request->limit);

        return $this->respondWithCollection($result, new ShippingPriceTransformer);
    }

    /**
     * Handles the request to delete a channel.
     * @param  string        $id
     * @param  DeleteRequest $request
     * @return Json
     */
    public function destroy($id)
    {
        try {
            $result = app('api')->shippingPrices()->delete($id);
        } catch (NotFoundHttpException $e) {
            return $this->errorNotFound();
        }

        return $this->respondWithNoContent();
    }
}
