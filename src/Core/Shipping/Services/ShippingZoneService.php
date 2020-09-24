<?php

namespace GetCandy\Api\Core\Shipping\Services;

use GetCandy;
use GetCandy\Api\Core\Scaffold\BaseService;
use GetCandy\Api\Core\Shipping\Models\ShippingZone;

class ShippingZoneService extends BaseService
{
    public function __construct()
    {
        $this->model = new ShippingZone();
    }

    /**
     * Create a shipping method.
     *
     * @param  array  $data
     * @return \GetCandy\Api\Core\Shipping\Models\ShippingZone
     */
    public function create(array $data)
    {
        $shipping = new ShippingZone;
        $shipping->fill($data);
        $shipping->save();

        if (! empty($data['countries'])) {
            $shipping->countries()->attach(
                GetCandy::countries()->getDecodedIds($data['countries'])
            );
        }

        return $shipping;
    }

    /**
     * Returns model by a given hashed id.
     *
     * @param  string  $id
     * @param  array|null  $includes
     * @return \GetCandy\Api\Core\Shipping\Models\ShippingZone
     *
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     */
    public function getByHashedId($id, $includes = null)
    {
        $id = $this->model->decodeId($id);

        $query = $this->model;

        if ($includes) {
            $query->with($includes);
        }

        return $query->findOrFail($id);
    }

    /**
     * Updates a shipping zone.
     *
     * @param  string  $id
     * @param  array  $data
     * @return \GetCandy\Api\Core\Shipping\Models\ShippingZone
     */
    public function update($id, array $data)
    {
        $shipping = $this->getByHashedId($id);
        $shipping->fill($data);

        $shipping->countries()->detach();

        if (! empty($data['countries'])) {
            $shipping->countries()->attach(
                GetCandy::countries()->getDecodedIds($data['countries'])
            );
        }

        $shipping->save();

        return $shipping;
    }

    public function getByCountryName($name)
    {
        $result = ShippingZone::with(['methods', 'methods.prices'])->whereHas('countries', function ($query) use ($name) {
            $query->where('name', $name);
        })->get();

        return $result;
    }

    public function getByName($name)
    {
        return ShippingZone::where('name', '=', $name)->first();
    }
}
