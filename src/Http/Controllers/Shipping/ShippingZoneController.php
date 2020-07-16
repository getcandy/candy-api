<?php

namespace GetCandy\Api\Http\Controllers\Shipping;

use GetCandy;
use GetCandy\Api\Http\Controllers\BaseController;
use GetCandy\Api\Http\Requests\Shipping\Zones\CreateRequest;
use GetCandy\Api\Http\Requests\Shipping\Zones\UpdateRequest;
use GetCandy\Api\Http\Resources\Shipping\ShippingZoneCollection;
use GetCandy\Api\Http\Resources\Shipping\ShippingZoneResource;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class ShippingZoneController extends BaseController
{
    /**
     * Returns a listing of shipping zones.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \GetCandy\Api\Http\Resources\Shipping\ShippingZoneCollection
     */
    public function index(Request $request)
    {
        $zones = GetCandy::shippingZones()->getPaginatedData(
            $request->per_page,
            $request->current_page,
            $this->parseIncludes($request->include)
        );

        return new ShippingZoneCollection($zones);
    }

    /**
     * Handles the request to show a shipping zone based on it's hashed ID.
     *
     * @param  string  $id
     * @param  \Illuminate\Http\Request  $request
     * @return array|\GetCandy\Api\Http\Resources\Shipping\ShippingZoneResource
     */
    public function show($id, Request $request)
    {
        try {
            $zone = GetCandy::shippingZones()->getByHashedId($id, $this->parseIncludes($request->include));
        } catch (ModelNotFoundException $e) {
            return $this->errorNotFound();
        }

        return new ShippingZoneResource($zone);
    }

    /**
     * Handles the request to create a new shipping zone.
     *
     * @param  \GetCandy\Api\Http\Requests\Shipping\Zones\CreateRequest  $request
     * @return \GetCandy\Api\Http\Resources\Shipping\ShippingZoneResource
     */
    public function store(CreateRequest $request)
    {
        $result = GetCandy::shippingZones()->create($request->all());

        return new ShippingZoneResource($result);
    }

    public function update($id, UpdateRequest $request)
    {
        try {
            $result = GetCandy::shippingZones()->update($id, $request->all());
        } catch (NotFoundHttpException $e) {
            return $this->errorNotFound();
        }

        return new ShippingZoneResource($result);
    }
}
