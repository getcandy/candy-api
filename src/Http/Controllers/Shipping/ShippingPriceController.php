<?php

namespace GetCandy\Api\Http\Controllers\Shipping;

use GetCandy;
use GetCandy\Api\Http\Controllers\BaseController;
use GetCandy\Api\Http\Requests\Shipping\Pricing\EstimateRequest;
use GetCandy\Api\Http\Requests\Shipping\Pricing\StoreRequest;
use GetCandy\Api\Http\Transformers\Fractal\Shipping\ShippingPriceTransformer;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class ShippingPriceController extends BaseController
{
    /**
     * Returns a listing of shipping prices.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function index(Request $request)
    {
        $orders = GetCandy::shippingPrices()->getPaginatedData($request->per_page, $request->current_page);

        return $this->respondWithCollection($orders, new ShippingPriceTransformer);
    }

    /**
     * Handles the request to show a shipping price based on it's hashed ID.
     *
     * @param  string  $id
     * @return array
     */
    public function show($id)
    {
        try {
            $channel = GetCandy::shippingPrices()->getByHashedId($id);
        } catch (ModelNotFoundException $e) {
            return $this->errorNotFound();
        }

        return $this->respondWithItem($channel, new ShippingPriceTransformer);
    }

    /**
     * Handles the request to create a new shipping price.
     *
     * @param  \GetCandy\Api\Http\Requests\Shipping\Pricing\StoreRequest  $request
     * @return array
     */
    public function store($id, StoreRequest $request)
    {
        $result = GetCandy::shippingPrices()->create($id, $request->all());

        return $this->respondWithItem($result, new ShippingPriceTransformer);
    }

    public function update($id, StoreRequest $request)
    {
        try {
            $result = GetCandy::shippingPrices()->update($id, $request->all());
        } catch (NotFoundHttpException $e) {
            return $this->errorNotFound();
        }

        return $this->respondWithItem($result, new ShippingPriceTransformer);
    }

    public function estimate(EstimateRequest $request)
    {
        $result = GetCandy::shippingPrices()->estimate($request->amount, $request->zip, $request->limit);

        return $this->respondWithCollection($result, new ShippingPriceTransformer);
    }

    /**
     * Handles the request to delete a shipping price.
     *
     * @param  string  $id
     * @return array|\Illuminate\Http\Response
     */
    public function destroy($id)
    {
        try {
            $result = GetCandy::shippingPrices()->delete($id);
        } catch (NotFoundHttpException $e) {
            return $this->errorNotFound();
        }

        return $this->respondWithNoContent();
    }
}
