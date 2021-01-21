<?php

namespace GetCandy\Api\Http\Controllers\Shipping;

use GetCandy;
use GetCandy\Api\Http\Controllers\BaseController;
use GetCandy\Api\Http\Requests\Shipping\CreateRequest;
use GetCandy\Api\Http\Requests\Shipping\UpdateRequest;
use GetCandy\Api\Http\Resources\Shipping\ShippingMethodCollection;
use GetCandy\Api\Http\Resources\Shipping\ShippingMethodResource;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class ShippingMethodController extends BaseController
{
    /**
     * Returns a listing of shipping methods.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \GetCandy\Api\Http\Resources\Shipping\ShippingMethodCollection
     */
    public function index(Request $request)
    {
        $methods = GetCandy::shippingMethods()->getPaginatedData($request->per_page, $request->current_page);

        return new ShippingMethodCollection($methods);
    }

    /**
     * Handles the request to show a shipping method based on it's hashed ID.
     *
     * @param  string  $id
     * @param  \Illuminate\Http\Request  $request
     * @return array|\GetCandy\Api\Http\Resources\Shipping\ShippingMethodResource
     */
    public function show($id, Request $request)
    {
        try {
            $shipping = GetCandy::shippingMethods()->getByHashedId($id, explode(',', $request->include));
        } catch (ModelNotFoundException $e) {
            return $this->errorNotFound();
        }

        return new ShippingMethodResource($shipping);
    }

    /**
     * Handles the request to create a new shipping method.
     *
     * @param  \GetCandy\Api\Http\Requests\Shipping\CreateRequest  $request
     * @return array
     */
    public function store(CreateRequest $request)
    {
        return new ShippingMethodResource(
            GetCandy::shippingMethods()->create($request->all())
        );
    }

    public function update($id, UpdateRequest $request)
    {
        try {
            $shippingMethod = GetCandy::shippingMethods()->update($id, $request->all());
        } catch (NotFoundHttpException $e) {
            return $this->errorNotFound();
        }

        return new ShippingMethodResource($shippingMethod);
    }

    public function updateZones($id, Request $request)
    {
        return new ShippingMethodResource(
            GetCandy::shippingMethods()->updateZones($id, $request->all())
        );
    }

    public function updateUsers($id, Request $request)
    {
        return new ShippingMethodResource(
            GetCandy::shippingMethods()->updateUsers($id, $request->users)
        );
    }

    public function deleteUser($methodId, $userId)
    {
        return new ShippingMethodResource(
            GetCandy::shippingMethods()->deleteUser($methodId, $userId)
        );
    }

    public function destroy($methodId)
    {
        try {
            GetCandy::shippingMethods()->delete($methodId);
        } catch (NotFoundHttpException $e) {
            return $this->errorNotFound();
        }

        return $this->respondWithSuccess();
    }
}
