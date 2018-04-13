<?php

namespace GetCandy\Api\Http\Controllers\Shipping;

use Illuminate\Http\Request;
use GetCandy\Api\Http\Controllers\BaseController;
use GetCandy\Api\Http\Requests\Shipping\CreateRequest;
use GetCandy\Api\Http\Requests\Shipping\UpdateRequest;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use GetCandy\Api\Http\Transformers\Fractal\Shipping\ShippingMethodTransformer;

class ShippingMethodController extends BaseController
{
    /**
     * Returns a listing of channels.
     * @return Json
     */
    public function index(Request $request)
    {
        $orders = app('api')->shippingMethods()->getPaginatedData($request->per_page, $request->current_page);

        return $this->respondWithCollection($orders, new ShippingMethodTransformer);
    }

    /**
     * Handles the request to show a shipping method based on it's hashed ID.
     * @param  string $id
     * @return Json
     */
    public function show($id)
    {
        try {
            $shipping = app('api')->shippingMethods()->getByHashedId($id);
        } catch (ModelNotFoundException $e) {
            return $this->errorNotFound();
        }

        return $this->respondWithItem($shipping, new ShippingMethodTransformer);
    }

    /**
     * Handles the request to create a new channel.
     * @param  CreateRequest $request
     * @return Json
     */
    public function store(CreateRequest $request)
    {
        $result = app('api')->shippingMethods()->create($request->all());

        return $this->respondWithItem($result, new ShippingMethodTransformer);
    }

    public function update($id, UpdateRequest $request)
    {
        try {
            $result = app('api')->shippingMethods()->update($id, $request->all());
        } catch (NotFoundHttpException $e) {
            return $this->errorNotFound();
        }

        return $this->respondWithItem($result, new ShippingMethodTransformer);
    }

    public function updateZones($id, Request $request)
    {
        $method = app('api')->shippingMethods()->updateZones($id, $request->all());

        return $this->respondWithItem($method, new ShippingMethodTransformer);
    }

    public function updateUsers($id, Request $request)
    {
        $method = app('api')->shippingMethods()->updateUsers($id, $request->users);

        return $this->respondWithItem($method, new ShippingMethodTransformer);
    }

    public function deleteUser($methodId, $userId)
    {
        $method = app('api')->shippingMethods()->deleteUser($methodId, $userId);

        return $this->respondWithItem($method, new ShippingMethodTransformer);
    }

    public function destroy($methodId)
    {
        try {
            $result = app('api')->shippingMethods()->delete($methodId);
        } catch (NotFoundHttpException $e) {
            return $this->errorNotFound();
        }

        return $this->respondWithSuccess();
    }
}
