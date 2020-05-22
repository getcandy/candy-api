<?php

namespace GetCandy\Api\Http\Controllers\Shipping;

use Illuminate\Http\Request;
use GetCandy\Api\Http\Controllers\BaseController;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use GetCandy\Api\Http\Requests\Shipping\Zones\CreateRequest;
use GetCandy\Api\Http\Requests\Shipping\Zones\UpdateRequest;
use GetCandy\Api\Http\Resources\Shipping\ShippingZoneResource;
use GetCandy\Api\Http\Resources\Shipping\ShippingZoneCollection;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class ShippingZoneController extends BaseController
{
    /**
     * Returns a listing of channels.
     * @return Json
     */
    public function index(Request $request)
    {
        $zones = app('api')->shippingZones()->getPaginatedData(
            $request->per_page,
            $request->current_page,
            $this->parseIncludes($request->include)
        );
        return new ShippingZoneCollection($zones);
    }

    /**
     * Handles the request to show a channel based on it's hashed ID.
     * @param  string $id
     * @return Json
     */
    public function show($id, Request $request)
    {
        try {
            $zone = app('api')->shippingZones()->getByHashedId($id, $this->parseIncludes($request->include));
        } catch (ModelNotFoundException $e) {
            return $this->errorNotFound();
        }

        return new ShippingZoneResource($zone);
    }

    /**
     * Handles the request to create a new channel.
     * @param  CreateRequest $request
     * @return Json
     */
    public function store(CreateRequest $request)
    {
        $result = app('api')->shippingZones()->create($request->all());
        return new ShippingZoneResource($result);
    }

    public function update($id, UpdateRequest $request)
    {
        try {
            $result = app('api')->shippingZones()->update($id, $request->all());
        } catch (NotFoundHttpException $e) {
            return $this->errorNotFound();
        }
        return new ShippingZoneResource($result);
    }
}
