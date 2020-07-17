<?php

namespace GetCandy\Api\Http\Controllers\Shipping;

use GetCandy;
use GetCandy\Api\Http\Controllers\BaseController;
use GetCandy\Api\Http\Requests\Shipping\Pricing\EstimateRequest;
use GetCandy\Api\Http\Requests\Shipping\Pricing\StoreRequest;
use GetCandy\Api\Http\Resources\Shipping\ShippingPriceCollection;
use GetCandy\Api\Http\Resources\Shipping\ShippingPriceResource;
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
        $prices = GetCandy::shippingPrices()->getPaginatedData($request->per_page, $request->current_page);

        return new ShippingPriceCollection($prices);
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
            $shippingPrice = GetCandy::shippingPrices()->getByHashedId($id);
        } catch (ModelNotFoundException $e) {
            return $this->errorNotFound();
        }

        return new ShippingPriceResource($shippingPrice);
    }

    /**
     * Handles the request to create a new shipping price.
     *
     * @param  \GetCandy\Api\Http\Requests\Shipping\Pricing\StoreRequest  $request
     * @return array
     */
    public function store($id, StoreRequest $request)
    {
        return new ShippingPriceResource(
            GetCandy::shippingPrices()->create($id, $request->all())
        );
    }

    public function update($id, StoreRequest $request)
    {
        try {
            $result = GetCandy::shippingPrices()->update($id, $request->all());
        } catch (NotFoundHttpException $e) {
            return $this->errorNotFound();
        }

        return new ShippingPriceResource($result);
    }

    public function estimate(EstimateRequest $request)
    {
        $result = GetCandy::shippingPrices()->estimate($request->amount, $request->zip, $request->limit);

        return new ShippingPriceResource($result);
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
            GetCandy::shippingPrices()->delete($id);
        } catch (NotFoundHttpException $e) {
            return $this->errorNotFound();
        }

        return $this->respondWithNoContent();
    }
}
